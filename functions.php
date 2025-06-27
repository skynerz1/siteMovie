<?php
if (!function_exists('getSeriesDetails')) {
    function getSeriesDetails($seriesId) {
        $files = ['search_results.json', 'search_arab.json'];

        foreach ($files as $filename) {
            if (file_exists($filename)) {
                $searchResults = json_decode(file_get_contents($filename), true);
                if (isset($searchResults['posters']) && is_array($searchResults['posters'])) {
                    foreach ($searchResults['posters'] as $item) {
                        if ($item['id'] == $seriesId && $item['type'] === 'serie') {
                            return $item;
                        }
                    }
                }
            }
        }

        $sources = ['created' => rand(1, 10), 'rating' => 1];
        foreach ($sources as $type => $page) {
            $url = "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$page}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
            $headers = ['User-Agent: okhttp/4.8.0', 'Accept-Encoding: gzip'];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'gzip'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if (is_array($data)) {
                foreach ($data as $item) {
                    if ($item['id'] == $seriesId && $item['type'] === 'serie') {
                        return $item;
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('getSeasonsAndEpisodes')) {
    function getSeasonsAndEpisodes($seriesId) {
        $url = "https://app.arabypros.com/api/season/by/serie/{$seriesId}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
        $headers = ['User-Agent: okhttp/4.8.0', 'Accept-Encoding: gzip'];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => 'gzip'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) return ['error' => 'Empty response'];
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => json_last_error_msg()];
        }

        return $data;
    }
}

if (!function_exists('safeOutput')) {
    function safeOutput($data) {
        if (is_array($data)) {
            return implode(', ', array_map('htmlspecialchars', $data));
        }
        return htmlspecialchars($data ?? '');
    }
}
