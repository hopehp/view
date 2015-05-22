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
         * View file finders
         *
         * @var Finder[]
         */
        protected $_finders;

        /**
         * View files directories
         *
         * @var array
         */
        protected $_folders;

        /**
         * View files extension
         *
         * @var string
         */
        protected $_extension = 'php';

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
                if (false === isset($this->_folders[$name])) {
                    $this->_folders[$name] = [];
                }
                $this->_folders[$name][] = $path;
            } else {
                $this->_folders[] = $path;
            }
            return $this;
        }

        /**
         * Returns named folder path
         *
         * @param string $name
         *
         * @return string|bool
         */
        public function getFolder($name)
        {
            if (isset($this->_folders[$name])) {
                return $this->_folders[$name];
            }
            return false;
        }

        /**
         * Returns registered folders
         *
         * @param string $name [optional]
         *
         * @return string[]
         */
        public function getFolders($name = null)
        {
            if (is_string($name) && isset($this->_folders[$name])) {
                return $this->_folders[$name];
            }
            return $this->_folders;
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
         * Set view files extension
         *
         * @param string $name
         *
         * @return \Hope\View\Engine
         */
        public function setExtension($name)
        {
            $this->_extension = ltrim($name, '.');

            return $this;
        }

        /**
         * Returns view files extension
         *
         * @return string
         */
        public function getExtension()
        {
            return $this->_extension;
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
         * Register files finder
         *
         * @param \Hope\View\Finder $finder
         *
         * @return \Hope\View\Engine
         */
        public function addFinder(Finder $finder)
        {
            $this->_finders[] = $finder;
            $finder->attach($this);

            return $this;
        }

        /**
         * Find view file path
         *
         * @param string $name
         *
         * @return bool|string
         */
        public function find($name)
        {
            if (file_exists($name)) {
                return $name;
            }

            foreach ($this->_finders as $finder) {
                if ($path = $finder->find($name)) {
                    return $path;
                }
            }

            throw new \InvalidArgumentException('View file ' . $name . ' not found');
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