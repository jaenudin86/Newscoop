<?php
$info = array( 
    'name' => 'blog',
    'version' => '0.1',
    'label' => 'Blogs',
    'description' => 'This plugin provides blogs.',
    'menu' => array(
        'name' => 'blog',
        'label' => 'Blog',
        'icon' => '/css/kedit.png',
        'sub' => array(
            array(
                'permission' => 'plugin_blog_admin',
                'path' => "blog/admin/list_blogs.php",
                'label' => 'Administrate Blogs',
                'icon' => 'css/gear.png',
            ),
            array(
                'permission' => 'plugin_blog_moderator',
                'path' => "blog/moderator/list_blogs.php",
                'label' => 'Moderate Blogs',
                'icon' => 'css/format_increaseindent.png',
            ),
            array(
                'permission' => 'plugin_blog_admin',
                'path' => "blog/admin/blog_prefs.php",
                'label' => 'Blog Preferences',
                'icon' => 'css/configure.png',
            ),
        ),
    ),
    'userDefaultConfig' => array(
        'plugin_blog' => 'N',
    ),
    'permissions' => array(
        'plugin_blog_admin' => 'User may manage Blogs',
        'plugin_blog_moderator' => 'User may moderate Blogs',
    ),
    'template_engine' => array(
        'objecttypes' => array(
            array('blog' => array('class' => 'Blog')),
            array('blogentry' => array('class' => 'BlogEntry')),
            array('blogcomment' => array('class' => 'BlogComment'))
        ),
        'listobjects' => array(
            array('blogs' => array('class' => 'Blogs', 'list' => 'blogs')),
            array('blogentries' => array('class' => 'BlogEntries', 'list' => 'blogentries')),
            array('blogcomments' => array('class' => 'BlogComments', 'list' => 'blogcomments'))
        ),
        'init' => 'plugin_blog_init'
    ),
    'localizer' => array(
        'id' => 'plugin_blog',
        'path' => '/plugins/blog/admin-files/blog/',
        'screen_name' => 'Blogs'
    ),
    'no_menu_scripts' => array(
    	'/blog/admin/blog_form.php',
    	'/blog/admin/entry_form.php',
    	'/blog/admin/comment_form.php',
    	'/blog/moderator/blog_form.php',
    	'/blog/moderator/entry_form.php',
    	'/blog/moderator/comment_form.php',
    ),
    'install' => 'plugin_blog_install',
    'enable' => 'plugin_blog_install',
    'update' => '',
    'disable' => '',
    'uninstall' => 'plugin_blog_uninstall'
);

if (!defined('PLUGIN_BLOG_FUNCTIONS')) {
    define('PLUGIN_BLOG_FUNCTIONS', true);

    function plugin_blog_install()
    {
        global $LiveUserAdmin, $g_documentRoot;
        
        $LiveUserAdmin->addRight(array('area_id' => 0, 'right_define_name' => 'plugin_blog_admin', 'has_implied' => 1));
        $LiveUserAdmin->addRight(array('area_id' => 0, 'right_define_name' => 'plugin_blog_moderator', 'has_implied' => 1)); 
        
        require_once($g_documentRoot.'/install/classes/CampInstallationBase.php');
        $GLOBALS['g_db'] =& $GLOBALS['g_ado_db'];
        
        $errors = CampInstallationBaseHelper::ImportDB(CS_PATH_PLUGINS.DIR_SEP.'blog'.DIR_SEP.'install'.DIR_SEP.'sql'.DIR_SEP.'plugin_blog.sql', $error_queries);
        
        unset($GLOBALS['g_db']);       
    }
    
    function plugin_blog_uninstall()
    {
        global $LiveUserAdmin, $g_documentRoot, $g_ado_db;
        
        foreach (array('plugin_blog_admin', 'plugin_blog_moderator') as $right_def_name) {
            $filter = array(
                "fields" => array("right_id"),
                "filters" => array("right_define_name" => $right_def_name)
            );
            $rights = $LiveUserAdmin->getRights($filter);
            if(!empty($rights)) {
                $LiveUserAdmin->removeRight(array('right_id' => $rights[0]['right_id']));
            }
        }
        
        $g_ado_db->execute('DROP TABLE plugin_blog_blog');
        $g_ado_db->execute('DROP TABLE plugin_blog_entry');
        $g_ado_db->execute('DROP TABLE plugin_blog_comment');        
        
        system('rm -rf '.CS_PATH_PLUGINS.DIR_SEP.'blog');    
    }
    
    function plugin_blog_init(&$p_context)
    {      
        $blog_id = Input::Get("f_blog_id", "int");
        $entry_id = Input::Get('f_blogentry_id', 'int');
        $comment_id = Input::Get('f_blogcomment_id', 'int');
        
        if (!empty($comment_id)) {
            $p_context->blogcomment = new MetaBlogComment($comment_id);
            $p_context->blogentry = new MetaBlogEntry($p_context->blogcomment->entry_id);
            $p_context->blog = new MetaBlog($p_context->blogcomment->blog_id);        
        } elseif (!empty($entry_id)) {
            $p_context->blogentry = new MetaBlogEntry($entry_id);
            $p_context->blog = new MetaBlog($p_context->blogentry->blog_id);        
        } elseif (!empty($blog_id)) {
            $p_context->blog = new MetaBlog($blog_id);      
        }
        
        foreach (array('f_blog', 
                       'f_blog_action',
                       'f_blogaction',
                       
                       'f_blog_id', 
                       'f_blog_title',
                       'f_blog_info',
                       'f_blog_request_text',
                       'f_blog_status',
                       'f_blogentry_action',
                       'f_blogcomment_action',
                       
                       'f_blogentry',
                       'f_blogentry_id',
                       'f_blogentry_title',
                       'f_blogentry_content',
                       'f_blogentry_mood',
                       
                       'f_blogcomment',
                       'f_blogcomment_id',
                       'f_blogcomment_title',
                       'f_blogcomment_content',
                       'f_blogcomment_user_name',
                       'f_blogcomment_user_email',
                       'f_blogcomment_mood',
                       'f_preview_blogcomment',
                       'f_submit_blogcomment',
                       'f_captcha_code'
                   ) as $v) {
                       
            $p_context->url->reset_parameter($v);
            $p_context->default_url->reset_parameter($v);   
        }
    }
       
    function plugin_blog_addPermissions()
    {
        $Admin = new UserType(1);
        $ChiefEditor = new UserType(2);
        $Editor = new UserType(3);
        
        $Admin->setPermission('plugin_blog_admin', true);
        $Admin->setPermission('plugin_blog_moderator', true);
        
        $ChiefEditor->setPermission('plugin_blog_moderator', true);
    }
}
?>