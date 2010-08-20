<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

if (!defined('PHP_SHLIB_SUFFIX')) {
    define('PHP_SHLIB_SUFFIX', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'dll' : 'so');
}

/**
 * If this gtk2 not already loaded attempt to do so
 */
if (!extension_loaded('gtk2')) {
    /**
     * Check if we can load gtk2 dynamically
     */
    $loadable = ini_get('enable_dl');
    if (!empty($loadable)) {
        dl('php_gtk2.' . PHP_SHLIB_SUFFIX);
    }
}

if (!class_exists('gtk')) {
    /**
     * TODO: retry with php -d and search for the .dll / .so
     */
    echo "Please load the php-gtk2 module in your php.ini\r\n";
    exit;
}

require 'Gorilla3D/Dom.php';
require 'Gorilla3D/Dom/Node.php';
require 'Chan/Parser.php';
require 'Fourchan/Sections.php';
require 'Fourchan/Parser.php';

class FourChanGui extends GtkWindow {

    function __construct($parent = null) {
        parent::__construct();

        /**
         * Do I really need this?
         */
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
        
        /*
            Allow multiple selections?
            $selection = $view->get_selection();
            $selection->set_mode(Gtk::SELECTION_MULTIPLE);
        */

        // Registar Events
        $veiw_selection = $this->view->get_selection();
        $veiw_selection->connect('changed', array($this, 'changed'));
        $this->view->connect('row_expanded', array($this, 'click_section'));
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

    public function click_section(GtkTreeView $view, GtkTreeIter $iter, array $path) {
        // Set the path to make sure the selection is selected
        $view->set_cursor($path);
        
        /** @var GtkTreeModel */
        $model = $view->get_model();
        
        $value = $model->get_value($iter, 0);
        
        $sections = Fourchan\Sections::getSections();
        $url = false;
        if(isset($sections[$value])) {
            $url = $sections[$value];
        } else {
            echo "fart!\n";
        }
        
        if($url) {
            $parser = new Fourchan\Parser($url);
            $parser->getPages();
            $parser->getThreads();
            // lots of threads >.<
            var_dump($parser->threads);
        }
        
        /** @var GtkTreeSelection */
        /* For multiple selections
        $selection = $view->get_selection();
            
        list($model, $arPaths) = $selection->get_selected_rows();
        echo "Selection is now:\r\n";
        foreach ($arPaths as $path) {
            $iter = $model->get_iter($path);
            echo '  ' . $model->get_value($iter, 0) . "\r\n";
        }
        */
        /*
        */
        echo "clicked Thread!" . PHP_EOL;
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
        $root = $this->store->append(null, array('/w/ - Anime/Wallpapers', '0'));
        $root2 = $this->store->append(null, array('/wg/ - Wallpapers/General', '0'));

        // Build Threads
        $child1 = $this->store->append($root, array('Thread 23897346', '0'));
        $child2 = $this->store->append($root, array('Thread 23897347', '0'));
        
        $child3 = $this->store->append($root2, array('Thread 23897348', '0'));
        $child4 = $this->store->append($root2, array('Thread 23897349', '0'));
    }

}

new FourChanGui();
Gtk::main();
?>
