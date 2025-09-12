<?php
class forms {
    public function signup() {
        ?>
        <form action="" method="post">
            <input type="text" name="first_name" placeholder="First name" required><br><br>
            <input type="text" name="last_name" placeholder="Last name" required><br><br>
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <?php $this->submit_button('Sign Up', 'signup'); ?>
            <br>
            <a href="signin.php">Already have an account? Login</a>
        </form>
        <?php
    }

    private function submit_button($value, $name) {
        ?>
        <button type="submit" name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></button>
        <?php
    }

    public function signin() {
        ?>
        <form action="" method="post">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <?php $this->submit_button('Sign In', 'signin'); ?>
            <a href="./">Dont have an account? Sign Up</a>

        </form>
        <?php
    }

}