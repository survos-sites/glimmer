<?php

namespace App\Dto;
use Symfony\Component\Serializer\Annotation\SerializedName;

class Zone
{
    #[SerializedName('Id')]
    public $id;
    #[SerializedName('StorageHostname')]
    public string $storageHostname;
//    #[SerializedName('Region')]
    public string $region;

}

