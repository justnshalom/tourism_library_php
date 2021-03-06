<?php
/*
 * COPYRIGHT NOTICE: 
 *
 * This file is part of CitySDK WP5 Tourism PHP Library.
 *
 * CitySDK WP5 Tourism PHP Library is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CitySDK WP5 Tourism PHP Library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with CitySDK WP5 Tourism PHP Library. If not, see <http://www.gnu.org/licenses/>.
 * 
 * Copyright 2013, IST
 */
 
require_once 'phar://citysdk-tourism.phar/TourismClient.php';
require_once 'phar://citysdk-tourism.phar/DataReader.php';

ob_start();

class CitySdkTests extends PHPUnit_Framework_TestCase {
	private $client;
	private $homeUrl = "http://polar-lowlands-9873.herokuapp.com/?list=backend";
	private $categories = array(
		'alojamento', 'hoteis', 'hostel', 'motel', 'musica'
	);
	private $locale = array('pt_PT', 'en_GB');
	private $reader = array(
		array( 
		  "Awolnation",  
		  "Awolnation ao vivo na TMN ao Vivo", 
		  "http://www1.sk-static.com/images/media/img/col6/20110322-001232-973681.jpg"
		),	
		array( 
		  "Sigur Ros",  
		  "Sigur Ros ao vivo no Campo Pequeno", 
		  "http://www1.sk-static.com/images/media/img/col6/20120930-091715-168615.jpg"
		),	
		array( 
		  "Mumford and Sons",  
		  "Mumford and Sons ao vivo no Coliseu de Lisboa", 
		  "http://www2.sk-static.com/images/media/img/col6/20110613-051124-257858.jpg"
		),	
	);

	public function setUp() {
		if(!isset($this->client)) {
			$this->client = new TourismClient($this->homeUrl);
			$this->client->useVersion('1.0');
		}
	}
	
    public function testListEvent() {
        $show = array(0, 19);
		$category = array("Music", "Notícias", "Stuff from Stuff");
		$params = array(
			"category" => $category,
			"tag" => "rock",
			"show" => $show	
		);

		$events = $this->client->getEvents($params);
		$url = "http://polar-lowlands-9873.herokuapp.com/v1/event/";
		$id = 1;
		foreach($events['event'] as $event) {
			$this->assertEquals($event['base'] + "" + $event['id'], $url + ($id++));
		}
    }
    
    private function getCategories($category) {
		if(isset($category['categories'])) {
			$categories = $category['categories'];
			foreach($categories as $cat) {
				$this->assertContains(DataReader::getLabel($cat, 'primary', 'pt_PT'), $this->categories);
				if (isset($cat['categories']) && count($cat['categories']) > 0) {
					$this->getCategories($cat);
				}
			}
		}
	}

	public function testCategories() {
		$params = array(
			"list" => 'poi',
		);
		$categories = $this->client->getCategories($params);
		$this->getCategories($categories);
	}
	
	public function testPoiWithId() {		
		$show = array(0, 19);
		$category = array('Museum', 'Garden');
		$params = array(
			'category' => $category,
			'show' => $show,
			'tag' => 'culture'
		);
	
		$poiList = $this->client->getPois($params);
		$pois = $poiList['poi'];
		$url = "http://polar-lowlands-9873.herokuapp.com/v1/poi/";
		$id = 1;
		foreach($pois as $poi) {
			$poi = $this->client->getPoi($poi['base'], $poi['id']);
			$this->assertEquals($poi['base'] . "" . $poi['id'], ($url . ($id++)));
		}
	}
	
	public function testAvailableLanguages() {
		$show = array(0, 19);
		$category = array('Museum', 'Garden');
		$params = array(
			'category' => $category,
			'show' => $show,
			'tag' => 'culture'
		);
	
		$pois = $this->client->getPois($params);
		foreach($pois['poi'] as $poi) {
			$langs = DataReader::getAvailableLanguages($poi);
			foreach($langs as $lang) {
				$this->assertContains($lang, $this->locale);
			}
		}
	}
	
	public function testDataReader() {
		$show = array(0, 19);
		$category = array('Music', 'Live');
		$tag = array('rock', 'indie');
		$params = array(
			'category' => $category,
			'show' => $show,
			'tag' => $tag
		);
		
		$events = $this->client->getEvents($params);
		$lang = 'pt_PT';
		$i = 0;
		foreach($events['event'] as $event) {
			$j = 0;
			
			$label = DataReader::getLabel($event, 'primary', $lang);
	        $description = DataReader::getDescription($event, $lang);
	        $img = DataReader::getThumbnails($event);
	        $imgContent = null;
	        $thumbnail = null;
	        if(count($img) > 0) {
	            $imgContent = $img[0];
	            $thumbnail = $imgContent->content;
	        }
	                
	        $this->assertEquals($label, $this->reader[$i][$j++]);
	        $this->assertEquals($description, $this->reader[$i][$j++]);
	        $this->assertNotNull($imgContent);
	        $this->assertTrue($imgContent->isUri);
	        $this->assertEquals($thumbnail, $this->reader[$i++][$j++]);
		}
	}
}
  
?>