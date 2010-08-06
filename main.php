<?php
error_reporting(E_ALL);
if (!defined('PHP_SHLIB_SUFFIX')) {
    define('PHP_SHLIB_SUFFIX', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'dll' :  'so');
}

if (!extension_loaded('gtk2')) {
    dl('php_gtk2.' . PHP_SHLIB_SUFFIX);
}

if(!class_exists('gtk')) {
	die('Please load the php-gtk2 module in your php.ini' . "\r\n");
}


class FourChanGui extends GtkWindow
{
	function __construct($parent = null)
	{
		parent::__construct();

		if ($parent)
			$this->set_screen($parent->get_screen());
		else
			$this->connect_simple('destroy', array('gtk', 'main_quit'));

		$this->set_title(__CLASS__);
		$this->set_position(Gtk::WIN_POS_CENTER);
		$this->set_default_size(-1, 500);
		$this->set_border_width(8);
		
        $hpaned = new GtkHPaned();
        $this->frame1 = new GtkFrame(null);
        $this->frame2 = new GtkFrame(null);
        
        $hpaned->add1($this->frame1);
        $hpaned->add2($this->frame2);
        
        
        $this->frame1->set_shadow_type(Gtk::SHADOW_IN);
        $this->frame2->set_shadow_type(Gtk::SHADOW_IN);
        
        $this->scrolled = new GtkScrolledWindow(null, null);
        $this->scrolled->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        
        $this->view = new GtkTreeView();
        
        // Registar Events
        $veiw_selection = $this->view->get_selection();
        $veiw_selection->connect('changed', array($this, 'changed'));
        $this->view->connect('row_expanded', array($this, 'click_thread'));
        $this->connect('destroy', array($this, 'main_quit'));
        
        $this->setup_treeview($this->view);
        $this->scrolled->add_with_viewport($this->view);
        $this->frame1->add($this->scrolled);
        
        $this->add($hpaned);
		
		$this->show_all();
	}
	
	public function main_quit() {
	    Gtk::main_quit();
	}
	
	public function changed() {
	
	}
	
	public function click_thread() {
	    echo "clicked Thread!";
	}

    protected function setup_treeview(GtkTreeView $view) {
        $this->store = new GtkTreeStore(Gobject::TYPE_STRING, Gobject::TYPE_STRING);
        $view->set_model($this->store);
        
        // Build Header
        $view->append_column(
            new GtkTreeViewColumn('4Chan Section', new GtkCellRendererText(), 'text', 0)
        );
        $view->append_column(
            new GtkTreeViewColumn('Photo Count', new GtkCellRendererText(), 'text', 1)
        );
        
        // Build Sections
        $root = $this->store->append(null, array('Anime Wallpaper', '0'));
        $root2 = $this->store->append(null, array('General Wallpaper', '0'));
        
        // Build Threads
        $child = $this->store->append($root, array('Thread 23897346', '0'));
    }
}

new FourChanGui();
Gtk::main();
?>
