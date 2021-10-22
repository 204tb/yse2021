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
	//入力値のチェック
	if (!is_numeric($_POST["price"]) || !is_numeric($_POST["stock"])) {
		//SESSIONの「error」に「数値以外が入力されています」と設定する。
		$_SESSION["success"]="金額、または在庫数に数値以外が入力されています";
		//「exit」関数で処理を終了する。
		header("Location:zaiko_ichiran.php");
		exit;
	}

	//POSTデータをサニタイズ
	$posts = check($_POST);
	//データベースに書籍を追加する
	Book_Add();
	//SESSIONの「success」に「入荷が完了しました」と設定する。
	$_SESSION["success"] ="書籍の追加が完了しました";
	//「header」関数を使用して在庫一覧画面へ遷移する。
	header("location:zaiko_ichiran.php");
	
}

//booksテーブル内の最大値となるidを取得
function getByid($con){
	$sql = "SELECT max(id) FROM books";
	$stmt = $con->prepare($sql);
	$stmt->execute();

	//⑫実行した結果から1レコード取得し、returnで値を返す。
	return $stmt->fetch();
}

//サニタイズ(XSS対策)
function check($posts)
{
    foreach ($posts as $column => $post) {
        $posts[$column] = htmlspecialchars($post, ENT_QUOTES, 'UTF-8');
    }
    return $posts;
}

//書籍を追加する
function Book_Add(){

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
					<?php 
    					// ⑯「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数にDBの接続情報を渡す。
						$book_id = getByid($pdo);

						//int型に変換して+1
						$book_id = intval($book_id['max(id)']) + 1; 
					?>
					<input type="hidden" value="" name="book">
					<tr>
						<td><?= $book_id;?></td>
						<td><input type='text' name='name' size='5' maxlength='11' required></td>
						<td><input type='text' name='author' size='5' maxlength='11' required></td>
						<td><input type='text' name='date' size='5' maxlength='255' required></td>
						<td><input type='text' name='price' size='5' maxlength='11' required></td>
						<td><input type='text' name='stock' size='5' maxlength='11' required></td>
					</tr>
				</table>
				<button type="submit" id="kakutei" formmethod="POST" name="new" value="ok">確定</button>
			</div>
		</div>
	</form>
	<!-- フッター -->
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>

?>