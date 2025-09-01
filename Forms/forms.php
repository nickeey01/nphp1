<?php
class forms{
    public function signup(){
?>
<form action='' method='post'>
    <input type='text' name='username' placeholder='Username' required><br><br>
    <input type='email' name='email' placeholder='Email' required><br><br>
    <input type='password' name='password' placeholder='Password' required><br><br>
    <button type='submit'>Sign Up</button> <a href='signin.php'>Already have an account? Login</a>
</form>
<?php
    }
    public function signin(){
?>
<form action='' method='post'>
    <input type='email' name='email' placeholder='Email' required><br><br>
    <input type='password' name='password' placeholder='Password' required><br><br>
    <button type='submit'>Sign In</button> <a href='./'>Don't have an account? Sign Up</a>
</form>
<?php
    }
}