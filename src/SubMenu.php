<?php

namespace PostTypes;

trait SubMenu
{
    /**
     * Holds an array of all the defined SubMenus.
     *
     * @var array
     */

    private $pages = [];
    
    /**
     * A temparary array for storing the submenu before its registered by wp
     * TODO: Find a better way to do this
     * @var array
     *
     */
    private $submenus = [];

    public function add_submenu($pageTitle, $menuTitle, $capebilities = "manage_options")
    {
        $page_slug = sanitize_title($pageTitle);
        $this->submenus['submenu_slug'] = $this->slug . '_' . $page_slug . '_submenu_page';
        $this->submenus["page_title"] = $pageTitle;
        $this->submenus["menu_title"] = $menuTitle;
        $this->submenus["capeabilities"] = $capebilities;

        add_action('admin_menu', array($this, 'add_submenu_page'));
    }

    public function add_submenu_page()
    {
        add_submenu_page(
            "edit.php?post_type=$this->slug",
            $this->submenus['page_title'], /*page title*/
            $this->submenus['menu_title'], /*menu title*/
            'manage_options', /*roles and capabiliy needed*/
            $this->submenus['submenu_slug'],
            array($this, 'submenu_content')
        );
        $this->submenus = [];
    }

    public function submenu_content()
    {
        echo "";
    }

    /**
     * Add an Advanced Custom Field Submenu
     * @param $pageTitle
     * @param $menuTitle
     */
    public function add_acf_submenu_page($pageTitle, $menuTitle)
    {
        acf_add_options_sub_page(array(
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'parent_slug' => "edit.php?post_type=$this->slug",
        ));
    }
}