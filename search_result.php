<?php

if (session_status()==PHP_SESSION_NONE) {
	//セッションを開始する
	session_start();
}

//SESSIONの「login」フラグがfalseか判定する。「login」フラグがfalseの場合はif文の中に入る。
if (!$_SESSION["login"]){
	//SESSIONの「error2」に「ログインしてください」と設定する。
	$_SESSION["error2"]="ログインしてください";
	//ログイン画面へ遷移する。
	header("Location:login.php");
	exit;
}

//データベースへ接続し、接続情報を変数に保存する
//データベースで使用する文字コードを「UTF8」にする
$db_name = "zaiko2021_yse";
$db_host = "localhost";
$db_charset = "utf8";

$dsn ="mysql:dbname={$db_name};host={$db_host};charset={$db_charset}";
$user ="zaiko2021_yse";
$pass ="2021zaiko";
try{
	$pdo = new PDO($dsn,$user,$pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//PDO::の後にオプションを付ける
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $e){
	echo "接続エラー";
	exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$keyword = "";
	$period = "";
	$price = "";
	$stock = "";
	$pe_rimit = 0;
	$pr_rimit = 0;

	//WHERE句を格納する変数
	$where = "";
	//各条件のsqlを格納する配列
	$where_array = [];
	
	if($_POST['keyword'] != 0 && empty($_POST['keyword'])){}
	else{
		//POSTデータを変数に格納,サニタイズ
		$keyword = check($_POST['keyword']);
		$where_array[] = "(title LIKE '%{$keyword}%' OR author LIKE '%{$keyword}%')";
	}

	if(!empty($_POST['period'])){
		//POSTデータを変数に格納
		$period = intval($_POST['period']);
		//範囲指定用変数periodの上限値
		$pe_rimit = 0;
		//periodの上限値設定 範囲：十の位
		for($i = $period ; substr($i,-2,1) === substr($period,-2,1) ; $i++)
		$pe_rimit = $i;
		$where_array[] = "SUBSTRING(salesDate,1,4)+0 BETWEEN {$period} AND {$pe_rimit}";
	}
	
	if(!empty($_POST['price'])){
		//POSTデータを変数に格納
		$price = intval($_POST['price']);
		//範囲指定用変数priceの上限値
		$pr_rimit = 0;
		//priceの上限値設定 範囲：百、千の位
		for($i = $price ; substr($i,0,1) === substr($price,0,1) ; $i++)
		$pr_rimit = $i;
		$where_array[] = "price BETWEEN {$price} AND {$pr_rimit}";
	}
	
	if(!empty($_POST['stock'])){
		//POSTデータの数値を変数に格納
		$stock = preg_replace('/[^0-9]/', '', $_POST['stock']);
		$stock = intval($stock);

		//POSTデータの下２桁を変数に格納
		$stockstr = mb_substr($_POST['stock'], -2, 2);

		//以上、未満の文字で判別しsqlを変える
		if($stockstr == "未満"){
			$where_array[] = "stock < {$stock}";
		}else if($stockstr == "以上"){
			$where_array[] = "stock >= {$stock}";
		}
	}

	if(count($where_array) > 0){
		//配列に格納したsqlにANDをつける
		for($i = 0; $i< count($where_array); $i++){
			$where_array[$i] = " AND ".$where_array[$i];
		}

		//WHERE句を生成、配列内のsqlをすべて連結
		$where =  " WHERE is_delete = false".implode($where_array);
		//SELECT句とWHERE句を連結
		$sql = "SELECT * FROM books".$where;
		//書籍テーブルから書籍情報を取得するSQLを実行する。また実行結果を変数に保存する
		$books = getbooks($pdo,$sql,$keyword,$period,$price,$stock,$pe_rimit,$pr_rimit);
	}
}

//サニタイズ(XSS対策)
function check($keyword)
{
    $keyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
    return $keyword;
}

function getbooks ($pdo,$sql,$keyword,$period,$price,$stock,$pe_rimit,$pr_rimit)
{
	$stmt = $pdo->prepare($sql);
	$stmt->execute();	
	$books = $stmt->fetchall(PDO::FETCH_ASSOC);
	return $books;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>入荷</title>
	<link rel="stylesheet" href="css/ichiran.css" type="text/css" />
</head>
<body>
	<!-- ヘッダ -->
	<div id="header">
		<h1>商品検索</h1>
	</div>

	<!-- メニュー -->
	<div id="menu">
		<nav>
			<ul>
				<li><a href="zaiko_ichiran.php?page=1">書籍一覧</a></li>
			</ul>
		</nav>
	</div>

	<form action="" method="post">
    <div id="pagebody">
			<div id="center">
				<table>
					<thead>
						<tr>
							<th id="check"></th>
							<th>ID</th>
							<th>書籍名</th>
							<th>著者名</th>
							<th>発売日</th>
							<th>金額(円)</th>
							<th>在庫数</th>
						</tr>
					</thead>
                    <?php
						if(!empty($books)){
							//SQLの実行結果の変数から1レコードのデータを取り出す。レコードがない場合はループを終了する。
							foreach($books as $book):
								//1レコードのデータを渡す。
								echo "<tr id='book'>";
								echo "<td id='check'><input type='checkbox' name='books[]'value=".$book['id']."></td>";
								echo "<td id='id'>{$book['id']}</td>";
								echo "<td id='title'>{$book['title']}</td>";
								echo "<td id='author'>{$book['author']}</td>";
								echo "<td id='date'>{$book['salesDate']}</td>";
								echo "<td id='price'>{$book['price']}</td>";
								echo "<td id='stock'>{$book['stock']}</td>";
	
								echo "</tr>";
							endforeach;							
						}else{
							echo "書籍が見つかりませんでした";
						}
						?>
				</table>
				<button type="submit" id="btn1" formmethod="POST" name="decision" value="3" formaction="nyuka.php">入荷</button>
                <button type="submit" id="btn1" formmethod="POST" name="decision" value="4" formaction="syukka.php">出荷</button>
			</div>
		</div>
	</form>
	<!-- フッター -->
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>