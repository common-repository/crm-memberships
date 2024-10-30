<style type="text/css">
    .error,
    .hide {
        display: none;
    }

    .wrap h1.wp-heading-inline {
        display: inline-block;
        margin-right: 5px
    }

    .wp-header-end {
        visibility: hidden;
        margin: -2px 0 0;
    }

    .ntz-global-nav-menu .ntz-global-nav-menu__tab.ntz-global-nav-menu__fiv {
        background: none;
        color: #0073aa;
        border-bottom: 2px solid #0073aa
    }

    .form-field .wp-picker-input-wrap .button.wp-picker-default {
        width: auto
    }
</style>
<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap">

    <div id="ntzcrm-col-container" class="wp-clearfix">
        <h1 class="wp-heading-inline">Access Tags</h1>


        <div id="ntzcrm-col-left" class="ntzcrm-col-box">
            <div class="ntzcrm-col-wrap">

                <div class="form-wrap">
                    <h2>Add / Edit tag</h2>
                    <form id="addtag" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>?action=ntzcrm_add_tag" class="validate">
                        <div class="form-field term-slug-wrap">
                            <label for="tag-ID">Tag Id *</label>
                            <input name="Id" id="tag-id" type="text"  onkeypress="return ntzIsNumber(event)"  value="" maxlength="15" size="40">
                            <p>To edit any tag please enter the tag id. If tag id is blank it will create new tag.</p>
                        </div>

                        <div class="form-field form-required term-name-wrap">
                            <label for="tag-name">Name *</label>
                            <input name="TagName" id="tag-name" type="text" value="" size="40" min="0" aria-required="true" required="required">
                            <p>Fill the name of the tag.</p>
                        </div>

                        <div class="form-field form-required term-name-wrap">
                            <label for="tag-name">Subscribe Page URL</label>
                            <input name="planLink" id="plan-link" type="text" value="" size="40" min="0" aria-required="true">
                            <p>Write the subscribe page link</p>
                        </div>
                        <?php /*
    $categories = get_categories(array('taxonomy' => 'category', 'order' => 'ASC', 'orderby' => 'name'));
    
    if(!empty($categories)){ ?>
    <div class="form-field term-parent-wrap">
        <label for="parent">Category</label>
        <select name="CategoryId" id="parent" class="postform">
            <option value="0">None</option>
            <?php
            $catList=array();
            foreach($categories as $key => $category){
                $catList[$category->term_id]=$category->name;
                echo "<option class='level-0' value='".$category->term_id."'>".$category->name."</option>";
            } ?> 
        </select>
    </div> 
    <?php } */ ?>
                        <p class="submit m-0 p-0">
                            <input type="submit" name="submit" id="submit" class="button button-primary" value="Add / Edit Tag"><span class="spinner"></span>
                        </p>
                        <?php wp_nonce_field('ntzcrm-add-tag-post'); ?>
                    </form>
                </div>
            </div>
        </div><!-- /col-left -->

        <div id="ntzcrm-col-right">
            <div class="ntzcrm-col-wrap">
                <?php
                if (!class_exists('WP_List_Table')) {
                    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
                }
                class ntzcrm_tag_list extends WP_List_Table
                {
                    public $request;
                    public $tags = '';
                    public $get_cat_list = '';

                    function __construct()
                    {
                        global $status, $page;

                        parent::__construct(array(
                            'singular' => 'inquiry',
                            'plural' => 'inquiries',
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
                            $item['id']
                        );
                    }

                    function get_columns()
                    {
                        $columns = array(
                            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
                            'id' => __('Id', 'custom_table_example'),
                            'name' => __('Name', 'custom_table_example'),
                            'plan_link' => __('Plan Link', 'custom_table_example'),
                            // 'category' => __('Category', 'custom_table_example')
                        );
                        return $columns;
                    }

                    function get_sortable_columns()
                    {
                        $sortable_columns = array(
                            'id' => array('id', true),
                            'name' => array('name', false),
                        );

                        return $sortable_columns;
                    }

                    function column_id($item)
                    {
                        $actions = array(
                            'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
                        );

                        return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions));
                    }

                    function get_bulk_actions()
                    {
                        $actions = array(
                            // 'delete' => 'Delete'
                        );
                        return $actions;
                    }

                    function process_bulk_action()
                    {
                        global $wpdb;
                        if ('delete' === $this->current_action()) {
                            $id = isset($this->request['id']) ? sanitize_text_field($this->request['id']) : array();
                            // if (is_array($ids))
                            //     $ids = implode(',', $ids);
                            if (!empty($id)) {

                                $postTags = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . NTZCRMPRIFIX . "post_tags WHERE tag_id = %d", $id), ARRAY_A);
                                $idsArr = [];
                                if (isset($postTags) && !empty($postTags)) {
                                    foreach ($postTags as $postIds) {
                                        $idsArr['post_ids'][] = $postIds['post_id'];
                                    }
                                }

                                $userTags = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM " . $wpdb->prefix . NTZCRMPRIFIX . "user_tags WHERE tag_id = %d", $id), ARRAY_A);
                                if (isset($userTags) && !empty($userTags)) {
                                    foreach ($userTags as $userIds) {
                                        $idsArr['user_ids'][] = $userIds['user_id'];
                                    }
                                }

                                $userData = (isset($idsArr['user_ids']) && !empty($idsArr['user_ids'])) ? serialize(array_unique($idsArr['user_ids'])) : null;
                                $postData = (isset($idsArr['post_ids']) && !empty($idsArr['post_ids'])) ? serialize(array_unique($idsArr['post_ids'])) : null;

                                $tempTagsId = $wpdb->insert(
                                    $wpdb->prefix . NTZCRMPRIFIX . "deleted_tags",
                                    array(
                                        'tag_id' => $id,
                                        'user_tags' => $userData,
                                        'post_tags' => $postData,
                                        'modified' => date('Y-m-d H:i:s'),
                                        'created' => date('Y-m-d H:i:s')
                                    ),
                                    array('%s', '%s', '%s', '%s', '%s')
                                );

                                if (isset($tempTagsId) && !empty($tempTagsId)) {
                                    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . NTZCRMPRIFIX . "membership_tags WHERE id =%d",$id));
                                    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . NTZCRMPRIFIX . "post_tags WHERE tag_id =%d",$id));
                                    $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . NTZCRMPRIFIX . "user_tags WHERE tag_id =%d",$id));
                                } 
                                wp_redirect(admin_url() . '/admin.php?page=add-new-tag&rmcache=' . rand(1, 1000));
                            }
                        }
                    }


                    function prepare_items()
                    {
                        global $wpdb;
                        $per_page = 20;
                        $hidden = array();
                        $columns = $this->get_columns();
                        $sortable = $this->get_sortable_columns();
                        $this->_column_headers = array($columns, $hidden, $sortable);
                        $this->process_bulk_action();
                        $paged = isset($this->request['paged']) ? ($per_page * max(0, intval(sanitize_text_field($this->request['paged'])) - 1)) : 0;
                        $orderby = (isset($this->request['orderby']) && in_array($this->request['orderby'], array_keys($this->get_sortable_columns()))) ? sanitize_text_field($this->request['orderby']) : 'id';

                        $order = (isset($this->request['order']) && in_array($this->request['order'], array('asc', 'desc'))) ? ' ' . strtoupper(sanitize_text_field($this->request['order'])) : ' DESC';
                        $table_name = $wpdb->prefix . NTZCRMPRIFIX . 'membership_tags';
                        $keyword = (isset($this->request['tg']) && !empty($this->request['tg'])) ? sanitize_text_field(trim($this->request['tg'])) : '';
                        $conditions = "where 1=%d";
                        $parameter = null;

                        $countSql = $wpdb->prepare("SELECT COUNT(id) FROM $table_name where 1=%d",1);
                        $sql = $wpdb->prepare("SELECT * FROM $table_name $conditions ORDER BY $orderby $order LIMIT $per_page OFFSET $paged",1);
                        if (!empty($keyword)) {
                            // $conditions ="WHERE `name` LIKE '%".$keyword."%'";
                            $conditions = "WHERE `name` LIKE %s";
                            $parameter = '%' . $wpdb->esc_like($keyword) . '%';
                            if (is_numeric($keyword)) {
                                // $conditions ="WHERE `id` =".$keyword;
                                $conditions = "WHERE `id` = %d";
                                $parameter = $keyword;
                            }
                            $countSql = $wpdb->prepare("SELECT COUNT(id) FROM $table_name $conditions", $parameter);
                            $sql = $wpdb->prepare("SELECT * FROM $table_name $conditions ORDER BY %s %s LIMIT %d OFFSET %d ", $parameter, $orderby, $order, $per_page, $paged);
                        }
                        $total_items = $wpdb->get_var($countSql); // $countSql using prepare statement 
                        $dataArr = $this->items = $wpdb->get_results($sql, ARRAY_A); // $sql using prepare statement 

                        foreach ($dataArr as $index => $tag) {
                            //   $this->items[$index]["category"]=(!empty($tag['category_id'])&&isset($this->get_cat_list[$tag['category_id']]))?$this->get_cat_list[$tag['category_id']]:''; 
                            $this->items[$index]["plan_link"] = (!empty($tag['plan_link'])) ? '<a href="' .esc_url($tag['plan_link']). '" target="_blank">' . $tag['plan_link'] . '</a>' : '';
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
                $table = new ntzcrm_tag_list();

                $postPermission = new NtzCrmPostPermission();
                $request = $postPermission->ntzcrmRequests();
                $keyword = (isset($request['tg']) && !empty($request['tg'])) ? trim($request['tg']) : '';
                $table->prepare_items(); ?>
                <div class="wrap">
                    <form method="get" accept-charset="utf-8" id="searchtagform">
                        <p class="search-box">
                            <label class="screen-reader-text" for="search-search-input"><?php esc_html_e('Search by Tag id:'); ?></label>
                            <input type="search" id="searchtag" name="s" value="<?php echo (isset($keyword) && !empty($keyword)) ? esc_attr($keyword) : ''; ?>" placeholder="Enter Id or Tag name .." class="">
                            <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search'); ?>">
                        </p>
                        <?php wp_nonce_field('ntzcrm-search-tag-post'); ?>
                    </form>

                    <a style="margin-top: 4px;" href="<?php echo esc_url(admin_url('/admin-ajax.php?action=export_tag')); ?>" class="page-title-action button"><?php esc_html_e('Export'); ?></a>

                    <form id="posts-filter" method="get">
                        <?php // $table->search_box('Search tab by id', 'search');
                        ?>
                        <?php $table->display() ?>
                        <?php wp_nonce_field('ntzcrm-search-tag-post'); ?>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    jQuery('#searchtagform').submit(function(e) {
        e.preventDefault();
        window.location.href = '<?php echo esc_url(admin_url() . 'admin.php?page=add-new-tag&tg='); ?>' + jQuery("#searchtag").val();
    });
</script>