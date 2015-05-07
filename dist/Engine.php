<?php

namespace Hope\View
{

    /**
     * Class Engine
     *
     * @package Hope\View
     */
    class Engine
    {

        /**
         * Active Theme
         *
         * @var
         */
        protected $_theme;

        /**
         * View files directories
         *
         * @var array
         */
        protected $_folders;

        /**
         * Engine functions
         *
         * @var callable[]
         */
        protected $_functions;

        /**
         * Register folder
         *
         * @param string $name [optional]
         * @param string $path
         *
         * @return \Hope\View\Engine
         */
        public function addFolder($name, $path = null)
        {
            if (is_null($path)) {
                $path = $name; $name = null;
            }

            if (false === is_dir($path)) {
                throw new \InvalidArgumentException('Directory not found');
            }

            if (is_string($name)) {
                if (isset($this->_folders[$name])) {
                    throw new \LogicException("Path named $name already registered");
                }
                $this->_folders[$name] = $path;
            } else {
                $this->_folders[] = $path;
            }
            return $this;
        }

        /**
         * Register view function
         *
         * @param string   $name
         * @param callable $func
         *
         * @return \Hope\View\Engine
         */
        public function addFunction($name, $func)
        {
            $this->_functions[$name] = $func;

            return $this;
        }

        /**
         * Checks if function exists
         *
         * @param string $name
         *
         * @return bool
         */
        public function hasFunction($name)
        {
            return isset($this->_functions[$name]);
        }

        /**
         * Returns function
         *
         * @param string $name
         *
         * @return callable
         */
        public function getFunction($name)
        {
            return $this->_functions[$name];
        }

        /**
         * Make view for file
         *
         * @param string $file
         *
         * @return \Hope\View\View
         */
        public function create($file)
        {
            return new View($file, $this);
        }

        /**
         * Make view for file and returns rendered
         *
         * @param string $file
         * @param array  $data
         *
         * @return string
         */
        public function render($file, array $data = [])
        {
            return $this->create($file)->render($data);
        }


    }

}