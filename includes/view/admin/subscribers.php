<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ntzcrm_user_list extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    public $request;
    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'Subscriber',
            'plural' => 'Subscribers',
        ));
        $postPermission = new NtzCrmPostPermission();
        $this->request = $postPermission->ntzcrmRequests();
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['ID']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'display_name' => __('Name', NTZCRMPRIFIX . 'user_list'),
            'user_email' => __('E-Mail', NTZCRMPRIFIX . 'user_list'),
            'user_registered' => __('Registration date', NTZCRMPRIFIX . 'user_list'),
            'contact_id' => __('Contact ID', NTZCRMPRIFIX . 'user_list'),
            'user_membership' => __('Membership', NTZCRMPRIFIX . 'user_list'),
            // 'action' => __('Action', NTZCRMPRIFIX.'user_list'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'user_email' => array('E-Mail', false),
            'contact_id' => array('E-Mail', false),
            'user_registered' => array('Registration date', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array();
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->users; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($this->request['id']) ? wp_parse_id_list($this->request['id']) : array();

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ({$placeholders})", $ids));
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->users;
        $conditions = "";
        $hidden = array();
        $per_page = 10;
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $paged = isset($this->request['paged']) ? ($per_page * max(0, intval($this->request['paged']) - 1)) : 0;
        $orderby = (isset($this->request['orderby']) && in_array($this->request['orderby'], array_keys($this->get_sortable_columns()))) ? $this->request['orderby'] : 'user_registered';
        $order = (isset($this->request['order']) && in_array($this->request['order'], array('asc', 'desc'))) ? ' ' . strtoupper($this->request['order']) : ' DESC';
        $dateFrom = (isset($this->request['datefrom']) && !empty($this->request['datefrom'])) ? sanitize_text_field($this->request['datefrom']) : date("Y-m-d");
        $dateTo = (isset($this->request['dateto']) && !empty($this->request['dateto'])) ? sanitize_text_field($this->request['dateto']) : date("Y-m-d");

        $placeHolderValue = [];
        $conditions = "WHERE 1=%d ";
        $placeHolderValue[] = 1;
        if (isset($this->request['tag']) && !empty($this->request['tag'])) {
            $conditions .= "AND  tag_id = %d";
            $placeHolderValue[] = sanitize_text_field($this->request['tag']);
        } else {
            $conditions .= "AND `user_registered` BETWEEN %s AND %s";
            $placeHolderValue[] = $dateFrom . " 00:00:00";
            $placeHolderValue[] = $dateTo . " 23:59:59";
        }
        if (isset($this->request['s']) && !empty($this->request['s'])) {
            $and = ' AND ';
            $conditions .= " $and (`display_name` LIKE '%" . $wpdb->esc_like($this->request['s']) . "%' OR `user_email` LIKE '%" . $wpdb->esc_like($this->request['s']) . "%')";
        }

        $userTag = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
        $membershipTagTabel = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";

        $countSql = 'SELECT count(*) FROM `' . $userTag . '` 
        INNER JOIN `' . $membershipTagTabel . '` ON `' . $userTag . '`.`tag_id` = `' . $membershipTagTabel . '`.`id` 
        INNER JOIN `' . $table_name . '` ON `' . $userTag . '`.`user_id` = `' . $table_name . '`.`ID` ' . $conditions;

        $total_items = $wpdb->get_var($wpdb->prepare($countSql, $placeHolderValue), ARRAY_A);

        $sql = 'SELECT * FROM `' . $userTag . '` 
        INNER JOIN `' . $membershipTagTabel . '` ON `' . $userTag . '`.`tag_id` = `' . $membershipTagTabel . '`.`id` 
        INNER JOIN `' . $table_name . '` ON `' . $userTag . '`.`user_id` = `' . $table_name . '`.`ID` ' . $conditions . ' 
        GROUP BY user_id ORDER BY '.$orderby.' '.$order.' LIMIT %d, %d';
        $placeHolderValue[] = $paged;
        $placeHolderValue[] = $per_page;
        
        $this->items = $wpdb->get_results($wpdb->prepare($sql, $placeHolderValue), ARRAY_A);
        if (!empty($this->items)) {
            foreach ($this->items as $count => $user) {
                $contactId = get_user_meta($user['ID'], 'ntzcrm_contact_id', true);
                $tagNames = $comma = "";
                $userTags = ntzcrm_dbquery::_getUserTags($user['ID']);
                if (!empty($userTags)) {
                    foreach ($userTags as $tid => $tName) {
                        $tagNames .= $comma . $tid . " : " . $tName;
                        $comma = ", ";
                    }
                }
                $this->items[$count]['contact_id'] = (isset($contactId) && !empty($contactId)) ? $contactId : "";
                $this->items[$count]['user_membership'] = ($tagNames != "") ? $tagNames : "";
                unset($tagNames);
                unset($contactId);
            }
        }
        $totalPageCount=(!empty($total_items))?ceil($total_items / $per_page):0;
        $this->set_pagination_args(array(
            'total_items' => (!empty($total_items))?$total_items:0, // total items defined above
            'per_page' => (!empty($per_page))?$per_page:0, // per page constant defined at top of method
            'total_pages' => $totalPageCount // calculate pages count
        ));
    }
}

global $wpdb;
$postPermission = new NtzCrmPostPermission();
$request = $postPermission->ntzcrmRequests();
//$filterDate=(isset($request['filterdate'])&&!empty($request['filterdate']))?$request['filterdate']:"";
$dateFrom = (isset($request['datefrom']) && !empty($request['datefrom'])) ? sanitize_text_field($request['datefrom']) : "";
$dateTo = (isset($request['dateto']) && !empty($request['dateto'])) ? sanitize_text_field($request['dateto']) : "";
$filterTag = (isset($request['tag']) && !empty($request['tag'])) ? sanitize_text_field($request['tag']) : "";
$table = new ntzcrm_user_list();
$table->prepare_items(); ?>

<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>

    <h1><?php _e('Search Subscribers (by registration date or by Access Tag)') ?></h1>

    <script type="text/javascript">
        jQuery(document).ready(function($) { 
            jQuery(".bulkactions").append('<a style="margin-top: 4px;" href="<?php echo esc_url(admin_url('/').'admin-ajax.php?action=export_subscriber&datefrom=' . $dateFrom . '&dateto=' . $dateTo . '&tag='.$filterTag); ?>" class="page-title-action button">Export</a>');
        });
    </script>

    <br>
    <form id="online_admissions-table-date-range" method="GET">
        <input type="hidden" name="page" value="<?php esc_html_e($request['page'], NTZCRMPRIFIX); ?>" />
        <?php ?><div style="display:inline-block;width: 100%;">
            <!-- readonly="readonly" -->
            <input type="text" id="date_from" name="datefrom" value="<?php esc_attr_e($dateFrom);  ?>" placeholder="YYYY-MM-DD">
            <input type="text" id="date_to" name="dateto" value="<?php esc_attr_e($dateTo);  ?>" placeholder="YYYY-MM-DD" >
            OR
            <select name="tag" id="tag" style="width: 25%;">
                <option value=""><?php esc_html_e("Select Any Tag", NTZCRMPRIFIX); ?></option>
                <?php
                $geTags = ntzcrm_dbquery::_getMembershipTagsList();
                if (!empty($geTags)) {
                    foreach ($geTags as $geTagId => $geTag) { ?>
                        <option value="<?php esc_attr_e($geTagId); ?>" <?php if (isset($request['tag']) && !empty($request['tag']) && $request['tag'] == $geTagId) {
                                                                            echo "selected";
                                                                        } ?>> <?php esc_html_e($geTag, NTZCRMPRIFIX); ?></option>
                <?php }
                } ?>


            </select>
            <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e("Filter", NTZCRMPRIFIX); ?>">

        </div>
        <?php
        $table->search_box('Search subscribers', 'search');
        $table->display() ?>
        <?php wp_nonce_field('ntzcrm-sub-post'); ?>
    </form>

</div>