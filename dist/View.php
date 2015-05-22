<?php

namespace Hope\View
{

    /**
     * Class View
     *
     * @package Hope\View
     */
    class View
    {

        /**
         * View name
         *
         * @var string
         */
        protected $_name;

        /**
         * View file path
         *
         * @var string
         */
        protected $_file;

        /**
         * View data
         *
         * @var array
         */
        protected $_data = [];

        /**
         * Engine object
         *
         * @var \Hope\View\Engine
         */
        protected $_engine;

        /**
         * View layout
         *
         * @var \Hope\View\View
         */
        protected $_layout;

        /**
         * View blocks
         *
         * @var array
         */
        protected $_blocks = [];

        /**
         * Escaping flags
         *
         * @var int
         */
        protected static $_flags;

        /**
         * Create new view instance
         *
         * @param string            $file [optional]
         * @param \Hope\View\Engine $engine
         */
        public function __construct($file = null, Engine $engine)
        {
            if (is_string($file)) {
                file_exists($file)
                    ? $this->setFile($file)
                    : $this->setName($file);
            }

            $this->_engine = $engine;
        }

        /**
         * Render view
         *
         * @return string
         */
        public function __toString()
        {
            return $this->render();
        }

        /**
         * Register data for view
         *
         * @param array|string $name
         * @param mixed        $value [optional]
         *
         * @return \Hope\View\View
         */
        public function data($name, $value = null)
        {
            if (is_array($name)) {
                foreach ($name as $k => $value) {
                    $this->_data[$k] = $value;
                }
            } else {
                $this->_data[$name] = $value;
            }

            return $this;
        }

        /**
         * Bind data for view
         *
         * @param string $name
         * @param mixed  $value
         *
         * @return \Hope\View\View
         */
        public function bind($name, &$value)
        {
            $this->_data[$name] = $value;

            return $this;
        }

        /**
         * Returns view file path
         *
         * @return string
         */
        public function path()
        {
            return $this->_file;
        }

        /**
         * Check if view file exists
         *
         * @return bool
         */
        public function exists()
        {
            return file_exists($this->_file);
        }

        /**
         * @param array $data [optional]
         *
         * @return string
         */
        public function render(array $data = [])
        {
            return $this->data($data)->renderInternal();
        }

        /**
         * Internal renderer
         *
         * @throws \Exception
         *
         * @return string
         */
        protected function renderInternal()
        {
            if (false === $this->exists()) {
                throw new \Exception('View file does not exists');
            }
            // Extract view values
            extract($this->_data);
            // Short access to View instance
            $v = $this;

            ob_start();
            include($this->path());
            $content = ob_get_clean();

            if ($this->_layout) {
                return $this->_layout->block('content', $content);
            }

            return $content;
        }

        /**
         * Touch layout view
         *
         * @param string $file
         * @param array  $data [optional]
         *
         * @throws \Exception
         *
         * @return \Hope\View\View
         */
        public function layout($file, array $data = [])
        {
            if ($this->_layout) {
                throw new \Exception('Layout already touched for this view');
            }
            $this->_layout = $this->_engine->create($file, $data);

            return $this;
        }

        /**
         * Start view block
         *
         * @param string $name
         * @param string $content [optional]
         *
         * @return \Hope\View\View
         */
        public function block($name, $content = null)
        {
            if (false === is_string($name)) {
                throw new \LogicException('');
            }

            if (is_null($content)) {
                ob_start();
            }
            $this->_blocks[$name] = $content;

            return $this;
        }

        /**
         * End view block
         *
         * @param string $name
         *
         * @return \Hope\View\View
         */
        public function end($name)
        {
            if (false === isset($this->_blocks[$name])) {
                throw new \LogicException('You must start block with method View::block()');
            }
            $this->_blocks[$name] = ob_get_clean();

            return $this;
        }

        /**
         * Render batch to file
         *
         * @param string   $file
         * @param array    $data
         * @param callable $filter [optional]
         *
         * @return string
         */
        public function batch($file, $data, callable $filter = null)
        {
            $html = [];
            $view = new View($file);

            if ($data instanceof \IteratorAggregate) {
                $data = $data->getIterator();
            }

            foreach ($data as $item) {
               $html[] = $view->render($item);
            }

            return join("\n", $html);
        }

        /**
         * Escape string
         *
         * @param string    $value
         * @param mixed  ...$funcs
         *
         * @return mixed|string
         */
        public function escape($value, ...$funcs)
        {
            if (static::$_flags === null) {
                static::$_flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
            }
            $value = htmlspecialchars($value, static::$_flags, 'UTF-8');

            if ($funcs) {
                $value = $this->filter($value, ...$funcs);
            }

            return $value;
        }

        /**
         * Escape string
         *
         * @see View::escape()
         *
         * @param string    $value
         * @param mixed  ...$funcs
         *
         * @return mixed|string
         */
        public function e($value, ...$funcs)
        {
            return $this->escape($value);
        }

        /**
         * Filter value
         *
         * @param mixed              $value
         * @param string|callable ...$funcs
         *
         * @return mixed
         */
        public function filter($value, ...$funcs)
        {
            if (count($funcs) === 1 and is_string($funcs[0]) && strpos($funcs[0], '|') !== false) {
                $funcs = explode('|', $funcs[0]);
            }

            foreach ($funcs as $func) {
                if (is_string($func) && $this->_engine->hasFunction($func)) {
                    $func = $this->_engine->getFunction($func);
                }

                if (is_callable($func)) {
                    $value = call_user_func($func, $value, $this);
                }
            }

            return $value;
        }

        /**
         * Filter value
         *
         * @see View::filter()
         *
         * @param mixed              $value
         * @param string|callable ...$funcs
         *
         * @return mixed
         */
        public function f($value, ...$funcs)
        {
            return $this->filter($value, ...$funcs);
        }

        /**
         * Set view name
         *
         * @param string $name
         */
        public function setName($name)
        {
            $this->setFile($this->_engine->find($name));
        }

        /**
         * Set view file
         *
         * @param string $file
         *
         * @return \Hope\View\View
         */
        public function setFile($file)
        {
            $this->_file = $file;

            return $this;
        }

    }

}