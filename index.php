<?php
$UPLOAD_PASSWORD = "#PASSWORD";

/* DB */
$conn = mysqli_connect(
    "URL",
    "DB_host url",
    "Password",
    "dbname",
    3306
);
if(!$conn){ die("DB Error"); }

$access = false;
$error = "";
$msg = "";

/* PASSWORD CHECK */
if(isset($_POST['enter_pass'])){
    if($_POST['gate_pass'] === $UPLOAD_PASSWORD){
        $access = true;
    } else {
        $error = "Invalid password";
    }
}

/* UPLOAD */
if(isset($_POST['upload']) && isset($_POST['upload_pass'])){
    if($_POST['upload_pass'] !== $UPLOAD_PASSWORD){
        $error = "Access denied";
    } else {
        $folder = __DIR__ . "/uploads/";
        if(!is_dir($folder)) mkdir($folder,0755,true);

        $filename = basename($_FILES['file']['name']);
        $tmp = $_FILES['file']['tmp_name'];

        $server_path = $folder.$filename;
        $db_path = "uploads/".$filename;

        if(move_uploaded_file($tmp,$server_path)){
            mysqli_query($conn,
                "INSERT INTO files(filename,filepath) VALUES('$filename','$db_path')"
            );
            $msg = "File uploaded successfully";
            $access = true;
        } else {
            $error = "Upload failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Secure Storage</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

*{
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}
body{
    margin:0;
    min-height:100vh;
    background:#fafafa;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#111;
}
.wrapper{
    width:440px;
    padding:42px;
    background:#fff;
    border:1px solid #e6e6e6;
    border-radius:12px;
    animation:fade 0.45s ease;
}
h1{
    font-size:22px;
    font-weight:500;
    margin:0 0 26px;
    text-align:center;
}
input,button{
    width:100%;
    padding:12px;
    font-size:14px;
    border-radius:6px;
    margin-top:14px;
}
input{
    border:1px solid #ccc;
}
button{
    border:none;
    background:#111;
    color:#fff;
    cursor:pointer;
}
button:hover{
    background:#000;
}
.msg{
    margin-top:14px;
    text-align:center;
    font-size:13px;
    color:#555;
}
table{
    width:100%;
    margin-top:26px;
    border-collapse:collapse;
}
th,td{
    padding:10px 0;
    font-size:14px;
    border-bottom:1px solid #eee;
}
th{
    text-align:left;
    font-weight:500;
}
a{
    color:#111;
    text-decoration:underline;
}
.note{
    margin-top:24px;
    padding:14px 16px;
    background:#f9f9f9;
    border-left:3px solid #111;
    font-size:13px;
    line-height:1.6;
    color:#333;
}
.footer{
    margin-top:18px;
    text-align:center;
    font-size:12px;
    color:#777;
}

@keyframes fade{
    from{opacity:0;transform:translateY(8px)}
    to{opacity:1;transform:translateY(0)}
}
</style>
</head>

<body>

<div class="wrapper">

<?php if(!$access): ?>

    <!-- PASSWORD -->
    <h1>Private access</h1>
    <form method="post">
        <input type="password" name="gate_pass" placeholder="Enter password" required>
        <button type="submit" name="enter_pass">Continue</button>
    </form>
    <div class="msg"><?php echo $error; ?></div>

<?php else: ?>

    <!-- UPLOAD -->
    <h1>Upload file</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload_pass" value="<?php echo $UPLOAD_PASSWORD; ?>">
        <input type="file" name="file" required>
        <button type="submit" name="upload">Upload</button>
    </form>

    <div class="msg"><?php echo $msg; ?></div>

    <table>
        <tr>
            <th>File</th>
            <th>Download</th>
        </tr>
        <?php
        $res = mysqli_query($conn,"SELECT * FROM files ORDER BY id DESC");
        while($row=mysqli_fetch_assoc($res)){
            echo "<tr>
                <td>{$row['filename']}</td>
                <td><a href='{$row['filepath']}' download>Download</a></td>
            </tr>";
        }
        ?>
    </table>

    <div class="note">
        <strong>Note:</strong> This website is strictly intended for data storage related to academic projects and laboratory manuals only. Unauthorized or non-academic usage is discouraged.
    </div>

    <div class="footer">
        Confidential • password required on every access
    </div>

<?php endif; ?>

</div>

</body>
</html>
