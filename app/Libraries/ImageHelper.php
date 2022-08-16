<?php

class ImageHelper{

    public function getProfileImage($avatar)
    {
        return "uploads/profile"+$avatar;
    }
}