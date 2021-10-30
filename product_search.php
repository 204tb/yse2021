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

	<form action="search_result.php" method="post">
			<div id="center">
				<table>
					<thead>
						<tr>
							<th>キーワード</th>
							<th>発売年代</th>
							<th>金額（円）</th>
							<th>在庫数</th>
						</tr>
					</thead>
					<tr>
                        <td>
                            <input type='text' name='keyword' size='5' required>
                        </td>
                        <td>
                            <select name="period" required>
                                <option value="" hidden></option>
                                <option value="1970">1970年代</option>
                                <option value="1980">1980年代</option>
                                <option value="1990">1990年代</option>
                                <option value="2000">2000年代</option>
                                <option value="2010">2010年代</option>
                                <option value="2020">2020年代</option>
                            </select>
                        </td>
                        <td>
                            <select name="price" required>
                                <option value="" hidden></option>
                                <option value="400">400円代</option>
                                <option value="500">500円代</option>
                                <option value="600">600円代</option>
                                <option value="700">700円代</option>
                                <option value="800">800円代</option>
                                <option value="900">900円代</option>
                                <option value="1000">1000円代</option>
                                <option value="2000">2000円代</option>
                            </select>
                        </td>
                        <td>
                            <select name="stock" required>
                                <option value="" hidden></option>
                                <option value="10">10冊未満</option>
                                <option value="20">20冊未満</option>
                                <option value="30">30冊未満</option>
                                <option value="40">40冊未満</option>
                                <option value="50">50冊未満</option>
                                <option value="50">50冊以上</option>
                            </select>
                        </td>
					</tr>
				</table>
				<button type="submit" id="kakutei" formmethod="POST" name="search" value="ok">検索</button>
			</div>
		</div>
	</form>
	<!-- フッター -->
	<div id="footer">
		<footer>株式会社アクロイト</footer>
	</div>
</body>
</html>