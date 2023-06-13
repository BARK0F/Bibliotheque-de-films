<?php
declare(strict_types=1);

use Database\MyPdo;
use Entity\Collection\CastCollection;
use Entity\Movie;
use Entity\People;
use Html\AppWebPage;
use Entity\Collection\MovieCollection;
use Entity\Collection\ImageCollection;
use Entity\Collection\PeopleCollection;

# Les sécurités
$stmt = MyPdo::getInstance()->prepare(
    <<<SQL
            SELECT id
            FROM people
SQL
);
$stmt->execute();
$peoples = $stmt->fetchAll(PDO::FETCH_CLASS, people::class);

$idPeoples = array();

foreach ($peoples as $people){
    $idPeoples[] = $people->getId();
}

if(isset($_GET['peopleId']) && ctype_digit($_GET['peopleId']) && in_array($_GET['peopleId'],$idPeoples)) {
    $peopleId=$_GET['peopleId'];
} else {
    header('Location: index.php');
    exit();
}

$webpage = new AppWebPage();
$PeopleCollection = new PeopleCollection();
$movieCollection = new MovieCollection();
$imageCollection = new ImageCollection();
$CastCollection = new CastCollection();


$content = "
<div class='dropdown'>
    <button class='dropbtn'>redirection</button>
    <div class='dropdown-content'>
      <a href='index.php'>Menu principal</a>
      <a href='form.php?action=create'>Menu de création</a>
    </div>
  </div>
";

$webpage->appendCss("
.dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f9f9f9;
      min-width: 120px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
    }
    
    .dropdown:hover .dropdown-content {
      display: block;
    }
    
    .dropdown-content a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }
    
    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
");

$actor = $PeopleCollection->findById(intval($peopleId));

$content .= "<div class='principal_content'>";
if ($actor->getAvatarId() !== null){
    $content .="<img src='image.php?imageId={$actor->getAvatarId()}'>";
}else{
    $content .="<img src='Image/people_not_found.png' alt='dere'>";
}
$content.="<div class='nom'>{$actor->getName()}</div>";
$content.="<div class='PlaceOfBirth'>{$actor->getPlaceOfBirth()}</div>";

$content.="<div class='date_actor'>";
$content.="<div class='Birthday'>{$actor->getBirthday()}</div>";
$content.="<div class='Deathday'>{$actor->getDeathday()}</div>";
$content.="</div>";

$content.="<div class='bio'>{$actor->getBiography()}</div>";

$content .= "</div>";


$movies = $movieCollection->findByPeopleId($actor->getId());

foreach ($movies as $movie){
    $image = $imageCollection->findById($movie->getPosterId());
    $content .="<a href='Movie.php?id={$movie->getId()}";
    $content .= "<div class='film'>";
    if ($image !== null){
        $content.= "<img class='poster_film' src='image.php?imageId={$image->getId()}'>";
    }else{
        $content .= "<img class='poster_film' src='Image/movie_not_found.png' alt='{$movie->getName()}'>";
    }
    $content.= "<div class='ligne1'>";
    $content.= "<div class='titre'>{$movie->getTitle()}</div>";
    $content.= "<div class='date'>{$movie->getReleasedate()}</div>";
    $content.="</div>";

    $cast = $CastCollection->findByMovieIdAndPeopleId($movie->getId(), $actor->getId());
    $content .= "<div class='role'>{$cast->getRole()}</div>";
    $content.="</a></div>";
}

$webpage->appendContent($content);
echo $webpage->toHtml();