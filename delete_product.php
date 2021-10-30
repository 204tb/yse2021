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

//POSTの「books」の値が空か判定する。空の場合はif文の中に入る。
if(empty($_POST["books"])){
	//SESSIONの「success」に「削除する商品が選択されていません」と設定する。
	$_SESSION["success"] ="削除する商品が選択されていません";
	//在庫一覧画面へ遷移する。
	header("Location:zaiko_ichiran.php");
	exit;
}

if(isset($_POST["delete"]) && $_POST["delete"]=="ok"){
	//書籍数をカウントするための変数を宣言し、値を0で初期化する。
	$books =0;
	//POSTの「books」から値を取得し、変数に設定する。
	
	foreach($_POST["books"] as $book){
		//「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数にbookとDBの接続情報を渡す。
		$book_data = getByid($book,$pdo);
		//「deleteByid」関数を呼び出す。その際に引数にbookとDBの接続情報を渡す。
		deleteByid($book,$pdo);
		//インクリメントでbooksの値を1増やす。
		$books++;
	}
	
	//SESSIONの「success」に「入荷が完了しました」と設定する。
	$_SESSION["success"] ="削除が完了しました";
	//在庫一覧画面へ遷移する。
	header("location:zaiko_ichiran.php");
	exit;
}

function getByid($id,$con){
	/* 
	 * 書籍を取得するSQLを作成する実行する。
	 * その際にWHERE句でメソッドの引数の$idに一致する書籍のみ取得する。
	 * SQLの実行結果を変数に保存する。
	 */
	$sql = "SELECT * FROM books WHERE :id =id";
	$stmt = $con->prepare($sql);
	$stmt->execute([":id" => $id]);

	//実行した結果から1レコード取得し、returnで値を返す。
	return $stmt->fetch();
}

function deleteByid($id,$con){
	/*
	 * 書籍情報のdeleteをtrueに更新するSQLを実行する。
	 * それにより、選択した本が在庫一覧に表示されなくなる
	 * 
	 */
	$sql = "UPDATE books SET is_delete=true WHERE :id = id";
	$stmt = $con->prepare($sql);
	$stmt->execute([":id" => $id]);
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
		<h1>商品削除</h1>
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
					/*
					 * POSTの「books」から一つずつ値を取り出し、変数に保存する。
					 */
    				foreach($_POST["books"] as $id){
    					//「getByid」関数を呼び出し、変数に戻り値を入れる。その際引数にidとDBの接続情報を渡す。
						$book = getByid($id,$pdo);
					?>
					<input type="hidden" value="<?= $book["id"];?>" name="books[]">
					<tr>
						<td><?= $book["id"];?></td>
						<td><?= $book["title"];?></td>
						<td><?= $book["author"];?></td>
						<td><?= $book["salesDate"];?></td>
						<td><?= $book["price"];?></td>
						<td><?= $book["stock"];?></td>
					</tr>
					<?php
					 }
					?>
				</table>
				<button type="submit" id="kakutei" formmethod="POST" name="delete" value="ok">確定</button>
			</div>
		</div>
	</form>
	<!-- フッター -->
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>