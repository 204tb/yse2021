<?php
/* 
【機能】
書籍テーブルより書籍情報を取得し、画面に表示する。
商品をチェックし、ボタンを押すことで入荷、出荷が行える。
ログアウトボタン押下時に、セッション情報を削除しログイン画面に遷移する。

【エラー一覧（エラー表示：発生条件）】
入荷する商品が選択されていません：商品が一つも選択されていない状態で入荷ボタンを押す
出荷する商品が選択されていません：商品が一つも選択されていない状態で出荷ボタンを押す
*/

//①セッションを開始する
session_start();

//②SESSIONの「login」フラグがfalseか判定する。「login」フラグがfalseの場合はif文の中に入る。
if ($_SESSION['login'] == false){
	//③SESSIONの「error2」に「ログインしてください」と設定する。
	$_SESSION['error2'] = "ログインしてください";
	//④ログイン画面へ遷移する。
	header('Location: login.php');
}

//⑤データベースへ接続し、接続情報を変数に保存する
//⑥データベースで使用する文字コードを「UTF8」にする
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

//⑦書籍テーブルから書籍情報を取得するSQLを実行する。また実行結果を変数に保存する
$books = getbooks($pdo);

//昇順、降順ボタンを押した場合
if(isset($_POST['asc'])){
	$column = $_POST['asc'];
	$books = Asc($books,$column);
}
else if(isset($_POST['desc'])){
	$column = $_POST['desc'];
	$books = Desc($books,$column);
}

function getbooks ($pdo, $limit = 20, $offset = 0)
{
	$sql = "SELECT * FROM books WHERE is_delete = false";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	
	$books = $stmt->fetchall(PDO::FETCH_ASSOC);
	
	return $books;
}

function Asc($books,$column){
	foreach ((array) $books as $key => $value) {
		$sort[$key] = $value[$column];
	}
	array_multisort($sort, SORT_ASC, $books);
	return $books;
}

function Desc($books,$column){
	foreach ((array) $books as $key => $value) {
		$sort[$key] = $value[$column];
	}
	array_multisort($sort, SORT_DESC, $books);
	return $books;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>書籍一覧</title>
	<link rel="stylesheet" href="css/ichiran.css" type="text/css" />
</head>
<body>
	<div id="header">
		<h1>書籍一覧</h1>
	</div>
	<form action="zaiko_ichiran.php" method="post" id="myform" name="myform">
		<div id="pagebody">
			<!-- エラーメッセージ表示 -->
			<div id="error">
				<?php
				/*
				 * ⑧SESSIONの「success」にメッセージが設定されているかを判定する。
				 * 設定されていた場合はif文の中に入る。
				 */ 

				if(isset($_SESSION['success'])){
					//⑨SESSIONの「success」の中身を表示する。
					echo $_SESSION['success'];
					$_SESSION["success"]="";
				}

				?>
			</div>
			
			<!-- 左メニュー -->
			<div id="left">
				<p id="ninsyou_ippan">
					<?php
						echo @$_SESSION["name"];
					?><br>
					<button type="button" id="logout" onclick="location.href='logout.php'">ログアウト</button>
				</p>
				<button type="submit" id="btn1" formmethod="POST" name="decision" value="3" formaction="nyuka.php">入荷</button>

				<button type="submit" id="btn1" formmethod="POST" name="decision" value="4" formaction="syukka.php">出荷</button>
	
				<button type="submit" id="btn1" formmethod="POST" name="decision" value="5" formaction="new_product.php">新商品追加</button>
				
				<button type="submit" id="btn1" formmethod="POST" name="decision" value="6" formaction="delete_product.php">商品削除</button>

				<button type="submit" id="btn1" formmethod="POST" name="decision" value="7" formaction="product_search.php">商品検索</button>

			</div>
			<!-- 中央表示 -->
			<div id="center">

				<!-- 書籍一覧の表示 -->
				<table>
					<thead>
						<tr>
							<th id="check"></th>
							<th id="id">ID</th>
							<th id="book_name">書籍名</th>
							<th id="author">著者名</th>
							<th id="salesDate">発売日<button type="submit" id="sort" name="asc" value="salesDate">▲</button><button type="submit" id="sort" name="desc" value="salesDate">▼</button></th>
							<th id="itemPrice">金額<button type="submit" id="sort" name="asc" value="price">▲</button><button type="submit" id="sort" name="desc" value="price">▼</button></th>
							<th id="stock">在庫数<button type="submit" id="sort" name="asc" value="stock">▲</button><button type="submit" id="sort" name="desc" value="stock">▼</button></th>
						</tr>
					</thead>
					<tbody>
						<?php
						//⑩SQLの実行結果の変数から1レコードのデータを取り出す。レコードがない場合はループを終了する。
						foreach($books as $book):
							//⑪extract変数を使用し、1レコードのデータを渡す。
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
						?>
					</tbody>
				</table>
			</div>
		</div>
	</form>
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>
