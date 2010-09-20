<?php
/**
 * creates a new htmlbin page
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

if (!input::arg('source') )
	throw new Exception('no source given');

$id		= page::create(input::arg('source'));

$link	= 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URL'].'?'.$id;

?>
<!DOCTYPE html>
<html>

<head>
	<title>htmlbin - page created!</title>
</head>

<body>

<h1>Your page has been created!</h1>

Here is the link: <a href="<?php echo $link; ?>"><?php echo $link; ?></a>

</body>
</html>
