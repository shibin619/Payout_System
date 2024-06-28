<?php include('db_conncet.php') ;

$sql = "SELECT user_id,id FROM `users`";
$result = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Registration for Affiliate Payout System </title>
    <link rel="stylesheet" href="style.css">
   </head>
<body>
  <div class="wrapper">
    <h2>Page To Add Affiliate Sales Members</h2>
    <form action="index.php" method="POST">
    <?php
    if ($_SESSION['form_error']) {
      print_r($_SESSION['form_error']);
    }
?>
      <div class="input-box">
        <input type="text" name="name" placeholder="Enter your name" required>
      </div>
      <div class="input-box">
        <input type="text" name="email" placeholder="Enter your email" required>
      </div>
      <div class="input-box">
        <select name="parent_id" id="parent_id">
            <option value="">Please select Referral ID</option>
            <?php if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) { ?>
                 <option value="<?=$row['id']?>"><?=$row['user_id']?></option>
                 <?php   }  } ?>
        </select>
      </div>

      <div class="input-box button">
        <input type="Submit" name="add_user" value="Register">
      </div>

    </form>
  </div>

</body>
</html>