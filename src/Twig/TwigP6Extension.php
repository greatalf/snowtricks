<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigP6Extension extends AbstractExtension
{
    public function getFilters()
    {
        return [ new TwigFilter('formatVideo', [$this, 'generateVideoIframe'], ['is_safe' => ['html']]) ];
    }

    function generateVideoIframe($url) {
        $iframe = '';

        // Vérifier si c'est un lien Dailymotion
        if (preg_match('#(?:https?:\/\/)?(?:www\.)?(?:dai\.ly|dailymotion\.com\/(?:embed\/video|video))\/([A-Za-z0-9]+)#', $url, $matches)) {
            $videoId = $matches[1];
            $iframe = '<iframe frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/' . $videoId . '" allowfullscreen="" title="Dailymotion Video Player"></iframe>';
        }

        // Vérifier si c'est un lien YouTube
        elseif (preg_match('#(?:https?:\/\/)?(?:www\.)?(?:youtu\.be|youtube\.com\/(?:embed\/|watch\?v=|v\/))([^\s&]+)#', $url, $matches)) {
            $videoId = $matches[1];
            $iframe = '<iframe frameborder="0" src="https://www.youtube.com/embed/' . $videoId . '?controls=0" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
        }

        return $iframe;
    }
}

// https://dai.ly/x6evv3
// <iframe frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/x6evv3" allowfullscreen="" title="Dailymotion Video Player"> </iframe>

// https://youtu.be/mBB7CznvSPQ
// <iframe  frameborder="0" src="https://www.youtube.com/embed/mBB7CznvSPQ?controls=0" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

