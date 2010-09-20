<?php
/**
 * htmlbin interface
 *
 *     htmlbin Copyright (C) 2010  Callan Bryant <callan1990@googlemail.com>
 *
 *     Based on Voswork - A simple, fast PHP filesystem abstraction layer
 *     Voswork Copyright (C) 2010  Callan Bryant <callan1990@googlemail.com>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package main
 * @author Callan Bryant <callan1990@googlemail.com>
 */

// load bootstrap to get access to the voswork environment
require dirname(__FILE__).'/../../core/bootstrap.php';

//load the input class (not necessary - the autoloader would. This is for speed.)
require_once CORE_DIR.'classes/input.class.php';

// see if a leaf needs to be loaded before attempting to load a wiki page
if ($leaf = input::arg('leaf'))
{
	kernel::load_leaf($leaf);
	die();
}

// the hosted pages will look nicer with ?Page definitions, so we won't use 
// the leaf autoloader - /?pagename instead
$query			= input::query();

// if page is null, the default leaf must be loaded
if (!$query)
	kernel::load_leaf('default');

else
	// load the page id given by the query
	page::view($query);

?>
