// Bu aşağıdaki kod ile Toplu olarak resimleri çıktı alabiliyoruz. ChatGPT prompt: Ortam dosyasında bulunana resimlerin meta datasını liste halinde çıktı almak istiyorum. Bu listeyi şu şekilde düzenle (sadece tüm resimleri listele) ve şu kolonlardan (title, Alt, Caption, Description, url)  oluşsun ekle.  Mediada bulunan tüm resimler için bu değerlerin çıktısını alsın? 15bin civarı resim var.

add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/download-media-csv/', [
        'methods' => 'GET',
        'callback' => 'download_media_csv',
    ]);
});

function download_media_csv($request) {
    // Bellek ve zaman sınırlarını artır
    ini_set('memory_limit', '512M');
    set_time_limit(0);

    // Medya kütüphanesindeki tüm resim dosyalarını al
    $args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => -1 // Tüm resimleri al
    );

    $images = get_posts($args);

    // CSV dosyası için başlıkları ayarla
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=media_images.csv');

    // Çıkışı aç
    $output = fopen('php://output', 'w');

    // CSV başlıkları yaz
    fputcsv($output, ['Title', 'Alt Text', 'Caption', 'Description', 'URL']);

    // Her bir resim için bilgileri çek ve CSV'ye yaz
    foreach ($images as $image) {
        // Başlık (Title)
        $title = get_the_title($image->ID);

        // Alt metin (Alt)
        $alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);

        // Caption (Açıklama)
        $caption = wp_get_attachment_caption($image->ID);

        // Description (Açıklama)
        $description = $image->post_content;

        // URL
        $url = wp_get_attachment_url($image->ID);

        // CSV'ye yaz
        fputcsv($output, [$title, $alt_text, $caption, $description, $url]);
    }

    fclose($output);
    exit; // İşlem tamamlandı
}
