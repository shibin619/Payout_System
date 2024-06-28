<?php include('db_conncet.php') ;
$user_details = array();
$_SESSION['payout_success'] =null;
$_SESSION['form_error'] =array();
$sql1 = "SELECT count(id) as `user_count` FROM `users`";
$result1 = $conn->query($sql1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_error'] =array();
    if (isset($_POST['add_user'])) {
        if (empty($_POST["name"]) || empty($_POST["email"]) || empty($_POST["parent_id"])) {
            $_SESSION['form_error'][] = "<span class='text-danger' >please check all input values</span>";
        }else{
            $name = validate($_POST['name']);
            $email = validate($_POST['email']);
            $ref_id = validate($_POST['parent_id']);
        }
        if ($result1->num_rows > 0) {
            while($row1 = $result1->fetch_assoc()) {
                $user_id = 'AFPS'.($row1['user_count']+1);
            }
        }
        // echo $user_id.'-'.$name.'-'.$email.'-'.$ref_id;die;

        $sql9 = "SELECT user_id FROM `users` WHERE user_id = '$user_id'";
        $result9= $conn->query($sql9);
        if ($result9->num_rows > 0) {
            while($row9 = $result9->fetch_assoc()) {
                if ($email == $row9['email']) {
                    $_SESSION['form_error'][] = "<span class='text-danger' >Email Already Exist. Please try with different Email</span>";
                }
            }
        }else{
            $sql2 = "INSERT INTO `users` (`id`, `name`,`email`, `user_id`, `ref_id`) VALUES (NULL, '$name', '$email','$user_id','$ref_id')";
            $result2= $conn->query($sql2);
        }
        if ($_SESSION['form_error']) {
            header("Location: http://localhost/Affiliate_payout_system/registration.php");
        }else{
            unset($_POST);
            $_SESSION['form_error'] =array();
            header("Location: http://localhost/Affiliate_payout_system/index.php");
        }
    }
    if (isset($_POST['add_sale'])) {
        $amount = validate($_POST['amount']) ?? 0;
        $user_id = validate($_POST['suserid']) ;
        $sql4 = "SELECT id FROM `users` WHERE user_id = '$user_id'";
        $result4= $conn->query($sql4);
        if ($result4->num_rows > 0) {
            while($row4 = $result4->fetch_assoc()) {
                $ref_id  = $row4['id'];
                $sql5 = "INSERT INTO `sales_details` (`sale_id`, `user_id`, `amount`, `created_date`) VALUES (NULL, '$ref_id', '$amount', NOW())";
                $result5= $conn->query($sql5);
                $sale_id = $conn->insert_id;
                $sql6 = "SELECT `percentage` FROM `payout_system`";
                $result6= $conn->query($sql6);
                if ($result6->num_rows > 0) {
                    while($row6= $result6->fetch_assoc()) {
                        $percentage = $row6['percentage'];
                        $commision_amount =  ($percentage/100)*$amount;
                        $sql7 = "SELECT ref_id,id FROM `users` WHERE id = $ref_id";
                        $result7= $conn->query($sql7);
                        if ($result7->num_rows > 0) {
                            while($row7= $result7->fetch_assoc()) {
                                if ($ref_id != 0) {
                                    $ref_id  = $aff_user_id = $row7['ref_id'];
                                    $sql8 = "INSERT INTO `commision_details` (`id`, `user_id`, `sale_id`, `amount`) VALUES (NULL, '$aff_user_id', '$sale_id', '$commision_amount')";
                                    $result8= $conn->query($sql8);
                                }
                                if ($ref_id == 0) {
                                    break;
                                }
                            }
                            $_SESSION['payout_success'] =1;
                        }
                    }
                }
            }
        }
        unset($_POST);
        header("Location: http://localhost/Affiliate_payout_system/index.php");
    }

}
$sql3 = "SELECT a.user_id,a.name,a.email,a.type,ifnull(sum(b.amount),0) as `amount` ,ifnull(sum(c.amount),0) as commision FROM `users` a LEFT JOIN sales_details b on b.user_id = a.id LEFT JOIN commision_details c on a.id =c.user_id GROUP BY a.user_id";
$result3 = $conn->query($sql3);
if ($result3->num_rows > 0) {
    while($row3 = $result3->fetch_assoc()) {
        $user_details[] = $row3;
    }
}
function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
?>
<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>
    <div class="container-fluid">
    <div class="row">
    <div class="col-md-10 mt-3"><h2>Affliate Users</h2></div>

    <div class="col-md-2 mt-5 mb-2">
    <a href="registration.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary">ADD Users</a>
    </div>
</div>
<table>
    <thead>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Sales Amount</th>
        <th>Commision</th>
        <th>Add Sales</th>
    </thead>
    <tbody>
        <?php
         foreach ($user_details as  $details) { ?>
            <tr id = "<?=$details['user_id']?>">
                <td><?=$details['user_id']?></td>
                <td><?=$details['name']?></td>
                <td><?=$details['email']?></td>
                <td><?=number_format((float)$details['amount'], 2, '.', '');?></td>
                <td><?=number_format((float)$details['commision'], 2, '.', '');?></td>
                <td> 
                    <?php if($details['type'] =="U") { ?>
                <button type="button" class="btn btn-success add_commision_btn" id="add_commision_btn"  data-bs-toggle="modal" data-bs-target="#add_commision" data-id ="<?=$details['user_id']?>">
                Add Sales</button>
                <?php } ?>
            </tr>
        <?php } ?>

    </tbody>
</table>
    </div>


</body>
<!-- The Modal -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
<div class="modal fade add_commision" id="add_commision">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Sales Details</h4>
        
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
 
            <div>
                <h6>User ID:</h6>
                <input type="text" id="suser_id" class="form-control suser_id"  disabled>
            </div>
            <div>
            <h6>Please Enter sales amount:</h6>

                <input type="hidden"  class="form-control" id="suserid" name="suserid" >
                <input type="number" name="amount" min=0 class="form-control" id="amount" required>
            </div>
 
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="Add Sale" name="add_sale">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
</form>
<script>
    <?php if ($_SESSION['payout_success']==1) { ?>
        alert('Commision distributed to affiliates');
    <?php 
        header("Location: http://localhost/Affiliate_payout_system/index.php");
    } ?>

    $(document).ready(function(){
        $('.add_commision_btn').click(function() {
            $('#suser_id,#suserid').val($(this).data('id'));
        });
    });

</script>
</html>

