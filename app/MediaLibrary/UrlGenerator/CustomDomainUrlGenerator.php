<?php

namespace App\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class CustomDomainUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        $url = parent::getUrl();

        $collection = $this->media->collection_name;


            $path = $this->getPathRelativeToRoot();
            return rtrim(env('ASSET_URL','http://localhost:8000/storage/'), '/') . '/' . $path;


        return $url;
    }
}
