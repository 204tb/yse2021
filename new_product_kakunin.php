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
}

//データベースへ接続し、接続情報を変数に保存する
//データベースで使用する文字コードを「UTF8」にする
$db_name = "zaiko2021_yse";
$db_host = "localhost";
$db_charset ="utf8";
$dsn ="mysql:dbname={$db_name};host={$db_host};charset={$db_charset}";
$user ="zaiko2021_yse";
$pass ="2021zaiko";
try{
	$pdo = new PDO($dsn,$user,$pass);
}catch(PDOException $e){
	$_SESSION["error2"]="データベースの接続に失敗しました";
	//ログイン画面へ遷移する。
	header("Location:login.php");
	exit;
}

//ボタンを押した際の処理
if(isset($_POST["new"]) && $_POST["new"]=="ok"){	
	//データベースに書籍を追加する
	Book_Add($pdo,$_SESSION['new_product'],$_SESSION['isbn']);
	//SESSIONの「success」に「入荷が完了しました」と設定する。
	$_SESSION["success"] ="書籍の追加が完了しました";
	//「header」関数を使用して在庫一覧画面へ遷移する。
	header("location:zaiko_ichiran.php");
}

//書籍を追加する
function Book_Add($pdo,$posts,$isbn){    
    try{
        $sql = "INSERT INTO books VALUES(id,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($posts['title'],$posts['author'],$posts['date'],$isbn,$posts['price'],$posts['stock'],false));
        header('Location: completion.php');
    }
    catch (PDOException $e) {
        header('Location: error_page.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>新商品追加</title>
	<link rel="stylesheet" href="css/ichiran.css" type="text/css" />
</head>
<body>
	<!-- ヘッダ -->
	<div id="header">
		<h1>新商品追加</h1>
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
			<!-- エラーメッセージ -->
			<div id="error">
			<?php
			/*
			 * ⑬SESSIONの「error」にメッセージが設定されているかを判定する。
			 * 設定されていた場合はif文の中に入る。
			 */ 
			if(isset($_SESSION["error"])){
				//⑭SESSIONの「error」の中身を表示する。
				echo '<p>'.$_SESSION["error"].'</p>';
				$_SESSION["error"]="";

			}
			?>
			</div>
			<div id="center">
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>書籍名</th>
							<th>著者名</th>
							<th>発売日</th>
							<th>金額(円)</th>
							<th>在庫数</th>
						</tr>
					</thead>
					<tr>
						<td><?= $_SESSION['book_id'];?></td>
                        <td><?= $_SESSION['new_product']['title'];?></td>
                        <td><?= $_SESSION['new_product']['author'];?></td>
                        <td><?= $_SESSION['new_product']['date'];?></td>
                        <td><?= $_SESSION['new_product']['price'];?></td>
                        <td><?= $_SESSION['new_product']['stock'];?></td>
					</tr>
				</table>
				<div id="kakunin">
					<p>
						上記の書籍を追加します。<br>
						よろしいですか？
					</p>
					<button type="submit" id="message" formmethod="POST" name="new" value="ok">はい</button>
					<button type="submit" id="message" formaction="new_product.php">いいえ</button>
				</div>
			</div>
		</div>
	</form>
	<!-- フッター -->
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>