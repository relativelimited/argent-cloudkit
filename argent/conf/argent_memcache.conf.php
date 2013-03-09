<?php
/**
 * Memcache Configuration
 * 
 * 
 * @package Argent CloudKit
 * @subpackage argent_database
 * @version 1.2.0
 * @since 1.2.1
 * @author Nick Cousins <me@nickcousins.co.uk>
 * @link http://www.argentcloudkit.com/documentation 
 */

$memcache_enabled = false;

if ($memcache_enabled){

/*
 * Key Prefix - use to add domain to keys
 */
$kp = 'argent';

/*
 * Memcache Servers
 */

$memcache = null;

global $memcache;

$memcache = new Memcached();

$memcache->addServer('localhost', 11211);


$memcache->setOption(Memcached::OPT_PREFIX_KEY, $kp);
}