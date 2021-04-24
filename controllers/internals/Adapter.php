<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    /**
     * Class to interact with adapters.
     */
    class Adapter extends \descartes\InternalController
    {
        private const ADAPTERS_FILES_END = 'Adapter.php';
        private const ADAPTERS_META_START = 'meta_';

        /**
         * List adapters with filepath and internal metas.
         *
         * @return array : ['adapter_filepath' => ['meta...' => value, ...], ...]
         */
        public function list_adapters()
        {
            $adapters = [];

            $files = $this->list_files();
            if (!$files)
            {
                return $adapters;
            }

            foreach ($files as $file)
            {
                $metas = $this->read_adapter_metas($file);
                if (!$metas)
                {
                    continue;
                }

                $adapters[$file] = $metas;
            }

            return $adapters;
        }

        /**
         * List Adapters files.
         *
         * @return mixed (false|array) : array of adapters files path
         */
        public function list_files()
        {
            if (!is_readable(PWD_ADAPTERS))
            {
                return false;
            }

            $adapters_files = [];

            $files = scandir(PWD_ADAPTERS);
            foreach ($files as $filename)
            {
                $len = mb_strlen(self::ADAPTERS_FILES_END);
                $end = mb_substr($filename, -$len);
                if (self::ADAPTERS_FILES_END !== $end)
                {
                    continue;
                }

                $adapters_files[] = PWD_ADAPTERS . '/' . $filename;
            }

            return $adapters_files;
        }

        /**
         * Read constants of an adapter.
         *
         * @param mixed $adapter_file
         *
         * @return mixed(array|bool) : False on error, array of constants name => value
         */
        public function read_adapter_metas($adapter_file)
        {
            $metas = [];

            if (!is_readable($adapter_file))
            {
                return false;
            }

            $adapter_classname = pathinfo($adapter_file, PATHINFO_FILENAME);
            $reflection_class = new \ReflectionClass('\adapters\\' . $adapter_classname);
            if (!$reflection_class)
            {
                return false;
            }

            $methods = $reflection_class->getMethods(\ReflectionMethod::IS_STATIC);
            foreach ($methods as $method)
            {
                $start_with = mb_substr($method->getName(), 0, mb_strlen(self::ADAPTERS_META_START));

                if (self::ADAPTERS_META_START !== $start_with)
                {
                    continue;
                }

                $metas[$method->getName()] = $method->invoke(null);
            }

            return $metas;
        }

        /**
         * List all adapters for a meta value
         * 
         * @param $search_name : Name of the meta
         * @param $search_value : Value of the meta
         *
         * @return array : Array with ['adapter filepath' => ['search_name' => value, ...], ...]
         */
        public function list_adapters_with_meta_equal($search_name, $search_value)
        {
            $adapters = $this->list_adapters();
            return array_filter($adapters, function($metas) use ($search_name, $search_value) {
                $match = false;
                foreach ($metas as $name => $value)
                {
                    if ($name === $search_name && $value === $search_value)
                    {
                        $match = true;
                    }
                }

                return $match;
            });
        }
    }
