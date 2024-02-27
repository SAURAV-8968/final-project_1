<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['submit'])){

   $id = create_unique_id();
   $Fname = $_POST['Fname'];
   $Fname = filter_var($Fname, FILTER_SANITIZE_STRING);
   $Lname = $_POST['Lname'];
   $Lname = filter_var($Lname, FILTER_SANITIZE_STRING);  
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 
   $c_pass = sha1($_POST['c_pass']);
   $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = create_unique_id().'.'.$ext;
   $image_tmp_name =$_FILES['image']['tmp_name'];
   $image_size =$_FILES['image']['size'];
   $image_folder = 'uploaded_images/' .$rename;


   

   $select_users = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select_users->execute([$email]);

   if($select_users->rowCount() > 0){
      $warning_msg[] = 'email already taken!';
   }else{
      if($pass != $c_pass){
         $warning_msg[] = 'Password not matched!';
      }else{
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{
            $insert_user = $conn->prepare("INSERT INTO `users`(id, Fname, Lname, number, email, password, image) VALUES(?,?,?,?,?,?,?)");
            $insert_user->execute([$id, $Fname,  $Lname, $number, $email, $c_pass, $rename]);
            move_uploaded_file($image_tmp_name, $image_folder);

            if($insert_user){
               $verify_users = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
               $verify_users->execute([$email, $pass]);
   
               $row = $verify_users->fetch(PDO::FETCH_ASSOC);
   
            
               if($verify_users->rowCount() > 0){
                  setcookie('user_id', $row['id'], time() + 60*60*24*30, '/');
                  header('location:index.php');
               }else{
                  $error_msg[] = 'something went wrong!';
               }
            }
         }

      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- register section starts  -->

<section class="form-container">

   <form action="" method="post" enctype="multipart/form-data">
      <h3>create an account!</h3>
      <input type="text" name="Fname" required maxlength="15" placeholder="enter your first name" class="box">
      <input type="text" name="Lname" required maxlength="15" placeholder="enter your last name" class="box">
      <input type="email" name="email" required maxlength="50" placeholder="enter your email" class="box">
      <input type="tel" name="number" required min="0" max="9999999999" maxlength="10" placeholder="enter your number" class="box">
      <input type="password" name="pass" required maxlength="20" placeholder="enter your password" class="box">
      <input type="password" name="c_pass" required maxlength="20" placeholder="confirm your password" class="box">
      <input type="file" name="image" class="box" required accept="image/*">
      <p>already have an account? <a href="login.php">login now</a></p>
      <input type="submit" value="register now" name="submit" class="btn">
   </form>

</section>

<!-- register section ends -->










<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>