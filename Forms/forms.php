<?php
class forms{
    public function signup(){
?>
<form action='' method='post'>
    <input type='text' name='username' placeholder='Username' required><br><br>
    <input type='email' name='email' placeholder='Email' required><br><br>
    <input type='password' name='password' placeholder='Password' required><br><br>
    <button type='submit'>Sign Up</button>
</form>
<?php
    } 
}