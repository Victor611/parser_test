<?php

	header('Content-Type: text/html; charset=utf-8');
	include 'simple_html_dom.php';

	$id=109;
	$count_authors = 258; //258 authors
	//
	$servername = "127.0.0.1";
	$username = "root";
	$password = "1";
	$dbname = "ukrlib";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname , 3306);
	// Check connection
	if ($conn->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error;
	}

	if (!($authors = $conn->prepare("INSERT INTO authors(id,name) VALUES (?,?)"))) {
     echo "Не удалось подготовить запрос: (". $conn->errno .")". $conn->error;
	}

	if (!($books = $conn->prepare("INSERT INTO books(author_id,title) VALUES (?,?)"))) {
     echo "Не удалось подготовить запрос: (". $conn->errno .")". $conn->error;
	}

	if (!$authors->bind_param("is", $id, $name)) {
		echo "Не удалось привязать параметры: (". $authors->errno .")". $authors->error;
	}

	if (!$books->bind_param("is", $id, $title)) {
		echo "Не удалось привязать параметры: (". $books->errno .")". $books->error;
	}
	
	while($id <= $count_authors){
		$id++;
		$html = file_get_html('http://ukrlib.com.ua/books/author.php?id='. $id);
		if($html){	
			foreach($html->find('h1') as $title){
				$name = addslashes($title->innertext);
				$name = iconv('CP1251', 'utf-8', $name);	
			}
			if (!$authors->execute()) {
			echo "Не удалось выполнить запрос: (". $authors->errno .")". $authors->error;
		}
			//echo '<br><hr>'.$id.':'.$name.'<hr><br>';
			$i=2;
			$page=1;
			while($page<$i){
				$data = file_get_html('http://ukrlib.com.ua/books/author.php?id='.$id.'&page='.$page);
				foreach($data->find('.list a') as $title){
					$title = addslashes($title->innertext);
					$title = iconv('windows-1251', 'utf-8',  $title);
					if (!$books->execute()) {
						echo "Не удалось выполнить запрос: (". $books->errno .")". $books->error;
					}
					//echo $id.':'.$title.'<br>';
				}
				
				foreach($data->find('div[class=content] h2 a') as $title){
					$title = addslashes($title->innertext);
					$title = iconv('windows-1251', 'utf-8',  $title);
					if (!$books->execute()) {
						echo "Не удалось выполнить запрос: (". $books->errno .")". $books->error;
					}
					//echo $id.':'.$title.'<br>';
				}
				
				foreach($data->find('a[class=next]') as $url){
					if($url->href)++$i;
				}
				
				$page++;
				$data->clear();
				unset($data);
			
			}
		}else continue;
		$html->clear();
		unset($html);
		
		
	}
		
	
//$authors->close();
//$books->close();
?>