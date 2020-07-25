<!DOCTYPE html>
<heml lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission5</title>
    </head>
    <body>
        <h1> けいじばんへようこそ </h1>
        
        <!--デフォルトメッセージ-->
            ※※※※※※※※※※※※※※※※※※※※※※※※※※※<br>
            コメントの編集や削除をしたい場合コメントを入力する際に<br>
            パスワードを設定する必要があります<br>
            パスワードを設定していない場合編集や削除はできません<br>
            ※※※※※※※※※※※※※※※※※※※※※※※※※※※<br>
        

        <!--POST送信-->
        投稿/編集フォーム<br>
        <form action="" method="post">
            <input type="num" name="elow" placeholder="編集したい投稿ID"><br>
            <input type="text" name="name" placeholder="名前">
            <input type="text" name="comm" placeholder="コメント">
            <input type="text" name="pass" placeholder="パスワード">
            <input type="submit" name="submit"><br><br>
        </form>
        
         削除フォーム<br>
        <form action="" method="post">
            <input type="num" name="dlow" placeholder="削除したい投稿ID">
            <input type="text" name="pass" placeholder="パスワード">
            <input type="submit" name="submit"><br><br>
        </form>

        <?php
            //DB接続設定
            $dsn = 'データベース名';
            $user = 'ユーザー名';
            $password = 'パスワード';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            //POST受信
            $name = $_POST["name"];
            $comm = $_POST["comm"];
            $pass = $_POST["pass"];
            $elow = $_POST["elow"];
            $dlow = $_POST["dlow"];

            //Postsを保管するテーブルを作成
            $sql = "CREATE TABLE IF NOT EXISTS Posts"
            ." ("
            . "id INT AUTO_INCREMENT PRIMARY KEY,"
            . "name char(32),"
            . "comm TEXT,"
            . "date TEXT,"
            . "edtd INT," //編集済みならば1、未編集ならば0が入るダミー変数
            . "dltd INT," //削除済みならば1、未削除ならば0が入るダミー変数
            . "pass TEXT"
            .");";
            $stmt = $pdo->query($sql);
            
            //Postされた行数の取得
            $nlow=0;
            $sql = 'SELECT * FROM Posts';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){$nlow++;}
            //echo "投稿が".$nlow."行あります<br>";

            //時刻の取得
            $date=date("Y/m/d H:i:s");

            //削除行は入力された？
            if($dlow!=""){
                //入力された数字は正しい？
                if(0<$dlow && $dlow<=$nlow){
                    //$dlow行目の投稿の内容を取得　<-これをしたい
                    $id = $dlow;                                    
                    $sql ="SELECT * FROM Posts WHERE id=:id";  
                    $stmt = $pdo->prepare($sql);      
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);   
                    $stmt->execute();                   
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                        $id = $row['id'];
                        $name = $row['name'];
                        $comm = $row['comm'];
                        $date = $row['date'];
                        $edtd = $row['edtd'];
                        $dltd = $row['dltd'];
                        $spass = $row['pass'];
                    }

                    //削除された行ではない？
                    if($dltd==0){
                        //パスワードは入力された？
                        if($pass!=""){
                            //パスワードは一致した？
                            if($pass == $spass){

                                //この行は削除するので削除ダミーを1に書き換える
                                $dltd = 1;

                                //データベースの$id(=$dlow)行目を上書きする
                                $sql = 'UPDATE Posts SET id=:id,name=:name,comm=:comm,date=:date,edtd=:edtd,dltd=:dltd WHERE id=:id';
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                                $stmt->bindParam(':comm', $comm, PDO::PARAM_STR);
                                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                                $stmt->bindParam(':edtd', $edtd, PDO::PARAM_INT);
                                $stmt->bindParam(':dltd', $dltd, PDO::PARAM_INT);
                                $stmt->execute();
                                echo "正常に削除されました<br>";
                                

                            }else{echo "パスワードが異なるか、設定されていません<br>";}
                        }else{echo "削除するにはパスワードの入力が必要です<br>";}
                    }else{echo "指定された行は既に削除されています<br>";}
                }else{echo "入力された削除行は不正です<br>";}
            }
            else{
                //編集行は入力された？
                if($elow!=""){
                    //入力された数字は正しい？
                    if(0<$elow && $elow<=$nlow){
                        //$elow行目の投稿の内容を取得  
                        $id = $elow;     
                        $sql ="SELECT * FROM Posts WHERE id=:id";  
                        $stmt = $pdo->prepare($sql);      
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);   
                        $stmt->execute();                   
                        $results = $stmt->fetchAll();
                         foreach ($results as $row){
                            $id = $row['id'];
                            $pname = $row['name'];
                            $pcomm = $row['comm'];
                            $pdate = $row['date']; 
                            $edtd = $row['edtd']; 
                            $dltd = $row['dltd'];
                            $spass = $row['pass'];
                        }
                        //既に削除された投稿ではない？
                        if($dltd==0){
                            //名前かコメントは入力された？
                            if($comm!=""|| $name!=""){
                                //パスワードは入力された？
                                if($pass!=""){
                                    //パスワードは一致した？
                                    if($pass == $spass){

                                        //編集内容を上書きして、編集ダミーを1に書き換える
                                        if($name!=""){$pname = $name;}
                                        if($comm!=""){$pcomm = $comm;}
                                        $edtd = 1;
                                        
                                        //データベースの$id(=$elow)行目を上書きする
                                        $sql = "UPDATE Posts SET id=:id,name=:name,comm=:comm,date=:date,edtd=:edtd,dltd=:dltd WHERE id=:id";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                                        $stmt->bindParam(':name', $pname, PDO::PARAM_STR);
                                        $stmt->bindParam(':comm', $pcomm, PDO::PARAM_STR);
                                        $stmt->bindParam(':date', $date, PDO::PARAM_INT);
                                        $stmt->bindParam(':edtd', $edtd, PDO::PARAM_INT);
                                        $stmt->bindParam(':dltd', $dltd, PDO::PARAM_INT);
                                        $stmt->execute();
                                        echo "正常に編集されました<br>";

                                    }else{echo "dltd:".$dltd." pass:".$pass."パスワードが異なるか、設定されていません<br>";} 
                                }else{echo "編集するにはパスワードの入力が必要です<br>";}
                            }else{echo "編集後の名前かコメントを入力してください<br>";}
                        }else{echo "既に削除された投稿は編集できません<br>";}    
                    }else{echo "入力された編集行は不正です<br>";}
                }
                else{
                    if($comm!=""){
                        if($name==""){$name = "名無しさん";}
                        if($pass==""){$pass = "?no_pass?";}
                        $edtd=0;
                        $dltd=0;

                        //データベースに書き込む
                        $sql = $pdo -> prepare("INSERT INTO Posts (name, comm, date, edtd, dltd, pass) VALUES (:name, :comm, :date, :edtd, :dltd, :pass)");
                        $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                        $sql -> bindParam(':comm', $comm, PDO::PARAM_STR);
                        $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                        $sql -> bindParam(':edtd', $edtd, PDO::PARAM_INT);
                        $sql -> bindParam(':dltd', $dltd, PDO::PARAM_INT);
                        $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                        $sql -> execute();
                        echo "コメント「".$comm."」を投稿しました<br>";

                    }
                    else{
                        if($name!=""){echo "コメントを入力してください<br>";}
                        else{echo "<br>";}
                    }   
                }
            }
            //Postsの表示
            echo "<><><><><><><><><><><><><><><><><><><><><><><><><><><br>";
            echo "ID <> 名前 <> コメント <> 投稿時刻<br><br>";
            $sql = "SELECT * FROM Posts";
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                if($row['dltd']==0){
                    echo $row['id'].' <> ';
                    echo $row['name'].' <> ';
                    echo $row['comm'].' <> ';
                    echo $row['date'].'  ';
                    if($row['edtd']==1){echo "(編集日時)";}
                    echo "<br>";
                }else{echo "このコメントは削除されています<br>";}    
                echo "<br>";
            }
            echo "<hr>";

        ?>

    </body>
<html>
