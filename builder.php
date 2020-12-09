<?php

date_default_timezone_set('Asia/Tokyo');
require __DIR__.'/vendor/autoload.php';

use Goutte\Client;
use FeedWriter\ATOM;
use Symfony\Component\DomCrawler\Crawler;

$Feed = new ATOM();
$Feed->setTitle('Yahoo Japan Marketing Solution Developer Center News');
$Feed->setLink('https://ads-developers.yahoo.co.jp/developercenter/ja/announcement/');
$Feed->setDate(new DateTime());
$Feed->setSelfLink('https://qcmnagai.github.io/yahoo-developercenter-news-feed/atom.xml');

//Create an empty Item
$client = new Client();
$crawler = $client->request('GET', 'https://ads-developers.yahoo.co.jp/developercenter/ja/announcement/');
$posts = $crawler->filter('li.Post__item--announce');
$latestUpdatedDatetime = '';
foreach($posts as $post) {
    $newItem = $Feed->createNewItem();

    $crawler = new Crawler($post);
    $link = $crawler->filter('a')->link();
    $title = $link->getNode()->textContent;
    $newItem->setTitle($title);

    $uri = $link->getUri();
    $newItem->setLink($uri);

    $dateString = $crawler->filter('p.Post__date')->getNode(0)->nodeValue;
    $updatedDatetime = \DateTime::createFromFormat('Y/m/d', $dateString);
    $updatedDatetime->setTime(0, 0, 0);
    $newItem->setDate($updatedDatetime);
    if ($latestUpdatedDatetime < $updatedDatetime) {
        $latestUpdatedDatetime = $updatedDatetime;
    }

    //$newItem->setDescription($title.'...');
    //$newItem->setContent($title.'...');
    $Feed->addItem($newItem);
}

if ($latestUpdatedDatetime) {
    $Feed->setDate($latestUpdatedDatetime);
}

file_put_contents(__DIR__.'/docs/atom.xml', $Feed->generateFeed(), LOCK_EX);
