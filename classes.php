<?php
// Creating a class
class HelloWorld {
    public function greet() {
        return "<h1>Hello, ICS!</h1>";
    }

    public function today() {
        return "<p>Today is " . date("l") . "</p>";
    }
}

// Creating an instance of the class
$hello = new HelloWorld();

// Using the class methods
print $hello->greet();
print $hello->today();