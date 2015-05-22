<?php

namespace Hope\View
{

    /**
     * Class Finder
     *
     * @package Hope\View
     */
    class Finder
    {

        /**
         * @var \Hope\View\Engine
         */
        protected $_engine;

        /**
         * Attach finder to engine
         *
         * @param \Hope\View\Engine $engine
         */
        public function attach(Engine $engine)
        {
            $this->_engine = $engine;
        }

        /**
         * Detach finder
         *
         * @return void
         */
        public function detach()
        {
            $this->_engine = null;
        }

        /**
         * Find file path from view name
         *
         * @param string $name
         *
         * @return string|bool
         */
        public function find($name)
        {
            if (false === is_string($name)) {
                throw new \InvalidArgumentException('View name must be a string');
            }

            $path = $name;
            $info = explode('::', $name);

            if (count($info) === 2) {
                $name = $info[0];
                $path = $info[1];
            } else if (count($info) > 2) {
                throw new \InvalidArgumentException('Do not use separator in view name more than once');
            }

            $folders = $name !== $path
                ? $this->_engine->getFolders($name)
                : $this->_engine->getFolders();

            if (null === pathinfo($path, PATHINFO_EXTENSION)) {
                $path .= '.' . $this->_engine->getExtension();
            }

            // Search file path in folders
            foreach ($folders as $folder) {
                $file = $folder . DIRECTORY_SEPARATOR . $path;

                if (file_exists($file)) {
                    return $file;
                }
            }

            return false;
        }
    }

}