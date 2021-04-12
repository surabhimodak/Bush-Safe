<?php


if (!class_exists('rtTPG')) {

    class rtTPG
    {
        public $options;
        public $post_type;
        public $assetsUrl;
        public $libPath;

        protected static $_instance;

        function __construct() {

            $this->options = array(
                'settings'          => 'rt_the_post_grid_settings',
                'version'           => RT_THE_POST_GRID_VERSION,
                'installed_version' => 'rt_the_post_grid_current_version'
            );
            $this->defaultSettings = array(
                'title_tag'  => 'h2',
                'custom_css' => null
            );

            $this->post_type = "rttpg";
            $this->libPath = dirname(__FILE__);
            $this->modelsPath = $this->libPath . '/models/';
            $this->classesPath = $this->libPath . '/classes/';
            $this->widgetsPath = $this->libPath . '/widgets/';
            $this->viewsPath = $this->libPath . '/views/';
            $this->assetsUrl = RT_THE_POST_GRID_PLUGIN_URL . '/assets/';

            $this->rtLoadModel($this->modelsPath);
            $this->rtLoadClass($this->classesPath);

        }

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        function rtLoadModel($dir) {
            if (!file_exists($dir)) {
                return;
            }
            foreach (scandir($dir) as $item) {
                if (preg_match("/.php$/i", $item)) {
                    require_once($dir . $item);
                }
            }
        }

        function rtLoadClass($dir) {
            if (!file_exists($dir)) {
                return;
            }
            $classes = array();
            foreach (scandir($dir) as $item) {
                if (preg_match("/.php$/i", $item)) {
                    require_once($dir . $item);
                    $className = str_replace(".php", "", $item);
                    $classes[] = new $className;
                }
            }
            if ($classes) {
                foreach ($classes as $class) {
                    $this->objects[] = $class;
                }
            }
        }

        function loadWidget($dir) {
            if (!file_exists($dir)) {
                return;
            }
            foreach (scandir($dir) as $item) {
                if (preg_match("/.php$/i", $item)) {
                    require_once($dir . $item);
                    $class = str_replace(".php", "", $item);
                    if (method_exists($class, 'register_widget')) {
                        $caller = new $class;
                        $caller->register_widget();
                    } else {
                        register_widget($class);
                    }
                }
            }
        }


        function render($viewName, $args = array(), $return = false) {
            global $rtTPG;
            $path = str_replace(".", "/", $viewName);
            $viewPath = $rtTPG->viewsPath . $path . '.php';
            if (!file_exists($viewPath)) {
                return;
            }
            if ($args) {
                extract($args);
            }
            if ($return) {
                ob_start();
                include $viewPath;

                return ob_get_clean();
            }
            include $viewPath;
        }

        /**
         * Dynamicaly call any  method from models class
         * by pluginFramework instance
         */
        function __call($name, $args) {
            if (!is_array($this->objects)) {
                return;
            }
            foreach ($this->objects as $object) {
                if (method_exists($object, $name)) {
                    $count = count($args);
                    if ($count == 0) {
                        return $object->$name();
                    } elseif ($count == 1) {
                        return $object->$name($args[0]);
                    } elseif ($count == 2) {
                        return $object->$name($args[0], $args[1]);
                    } elseif ($count == 3) {
                        return $object->$name($args[0], $args[1], $args[2]);
                    } elseif ($count == 4) {
                        return $object->$name($args[0], $args[1], $args[2], $args[3]);
                    } elseif ($count == 5) {
                        return $object->$name($args[0], $args[1], $args[2], $args[3], $args[4]);
                    } elseif ($count == 6) {
                        return $object->$name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                    }
                }
            }
        }
    }


    function rtTPG() {
        global $rtTPG;
        $rtTPG = rtTPG::instance();
        return rtTPG::instance();
    }

    rtTPG();
}
