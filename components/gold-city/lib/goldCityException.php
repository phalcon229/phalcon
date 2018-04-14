<?php

class goldCityException extends Exception {
    public function errorMessage()
    {
        return $this->getMessage();
    }
}