<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ntzcrm_publications_list extends WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    public $request;
    function __construct()
    {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'Publications',
            'plural' => 'publications',
        ));
        $postPermission=new NtzCrmPostPermission();
        $this->request=$postPermission->ntzcrmRequests();
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
            '<input type="checkbox" name="pubid[]" value="%s" />',
            $item['ID']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'post_title' => __('Publication', 'publication_list'),
            'post_status' => __('Status', 'publication_list'),
            'tags' => __('Tags', 'publication_list'),
            'short_code' => __('Short code', 'publication_list'),
            'action' => __('Action', 'publication_list'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'post_title' => array('post_title', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->posts; // do not forget about tables prefix

        if ('delete' === $this->current_action()) { 
            $ids = isset($this->request['pubid']) ?wp_parse_id_list($this->request['pubid']): array();  
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ({$placeholders})",$ids));
            }
        }
    }

    function prepare_items(){ 
        global $wpdb; 
        $placeholderValueArr=[];
        $placeholderArr[0]=1;
        $conditions = " WHERE 1 = %d AND ";
        $hidden = array();
        $per_page = 2;
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        $paged = isset($this->request['paged']) ? ($per_page * max(0, intval($this->request['paged']) - 1)) : 0;

        $orderby = (isset($this->request['orderby']) && in_array($this->request['orderby'], array_keys($this->get_sortable_columns()))) ?sanitize_text_field($this->request['orderby']): 'ID';

        $order = (isset($this->request['order']) && in_array($this->request['order'], array('asc', 'desc'))) ? ' ' . strtoupper(sanitize_text_field($this->request['order'])) : ' DESC';
      
        $placeholderValueArr[0]="1";
        $placeholderValueArr[1]="is_ntzcrm_publication";
        $placeholderValueArr[2]="yes";
        $placeholderValueArr[3]="page";

        $conditions .= " `meta_key`=%s AND `meta_value`=%s AND `post_type`=%s ";  //AND post_status='publish'
       
        if (isset($this->request['s']) && !empty($this->request['s'])) {
            $placeholderValueArr[4]="'%" .$wpdb->esc_like($this->request['s']). "%'";
            $placeholderValueArr[5]="'%" .$wpdb->esc_like($this->request['s']). "%'";
            $conditions .= "  (`post_name` LIKE %s OR `post_title` LIKE %s) GROUP BY id";
        } 
        $placeholderValueArr[6]=$paged;
        $placeholderValueArr[7]=$per_page; 
        $sql = "SELECT `$wpdb->posts`.ID,post_name,post_title,post_status FROM `$wpdb->posts` INNER JOIN `" . $wpdb->postmeta . "` ON `post_id`=`" . $wpdb->posts . "`.`ID` $conditions ORDER BY $orderby $order LIMIT %d,%d ";
        $total_items = $wpdb->get_var($wpdb->prepare("SELECT count(`$wpdb->posts`.ID) FROM `$wpdb->posts` INNER JOIN `" . $wpdb->postmeta . "` ON `post_id`=`" . $wpdb->posts . "`.`ID` $conditions",$placeholderValueArr));
        $this->items = $wpdb->get_results($wpdb->prepare($sql,$placeholderValueArr), ARRAY_A);
        if (!empty($this->items)) {
            foreach ($this->items as $count => $post) {
                $tagNames = $comma = "";
                $postTags = ntzcrm_dbquery::_getPostTags($post['ID']);
                foreach ($postTags as $tid => $tName) {
                    $tagNames .= $comma . $tid . " : " . $tName;
                    $comma = ", ";
                }
                $this->items[$count]['tags'] = ($tagNames != "") ? $tagNames : "";
                $this->items[$count]['short_code'] = "<span>[" . NTZCRMPRIFIX . "icon post_id='" . $post['ID'] . "']</span>";
                unset($tagNames);
                $this->items[$count]['action'] = "<a class='button' href='" . admin_url('admin.php?page=add-publication-wizard&pid=' . $post['ID']) . "'>Edit Wizard</a>";
                $this->items[$count]['action'] .= " <a class='button' href='" . admin_url('post.php?action=edit&post=' . $post['ID']) . "'>Add/Edit Content</a>";
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
$postPermission=new NtzCrmPostPermission();
$request=$postPermission->ntzcrmRequests();
$table = new ntzcrm_publications_list();
$table->prepare_items(); ?>

<style>
.ntz-global-nav-menu .ntz-global-nav-menu__tab.ntz-global-nav-menu__thr { background: none; color: #0073aa; border-bottom: 2px solid #0073aa }
</style> 
<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap wrap">
	<h1 class="wp-heading-inline"><?php _e('Publications') ?>&nbsp;</h1>
    <a href="<?php echo admin_url('admin.php?page=add-publication-wizard'); ?>" class="page-title-action"><?php esc_html_e('Add New Publication'); ?></a>
	
   <br>
    <form id="online_admissions-table-date-range" method="GET">
        <input type="hidden" name="page" value="<?php echo esc_attr($request['page']); ?>" />
        <?php
            $table->search_box('Search publication', 'search');
            $table->display();
        ?>
        <?php wp_nonce_field( 'ntzcrm-pub-post' ); ?>
    </form>
</div>