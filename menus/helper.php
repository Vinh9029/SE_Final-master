<?php
function generateSlug($string)
{
    // Chuyển sang chữ thường
    $slug = mb_strtolower($string, 'UTF-8');

    // Thay thế ký tự tiếng Việt có dấu
    $slug = str_replace(
        [
            'à',
            'á',
            'ạ',
            'ả',
            'ã',
            'â',
            'ầ',
            'ấ',
            'ậ',
            'ẩ',
            'ẫ',
            'ă',
            'ằ',
            'ắ',
            'ặ',
            'ẳ',
            'ẵ',
            'è',
            'é',
            'ẹ',
            'ẻ',
            'ẽ',
            'ê',
            'ề',
            'ế',
            'ệ',
            'ể',
            'ễ',
            'ì',
            'í',
            'ị',
            'ỉ',
            'ĩ',
            'ò',
            'ó',
            'ọ',
            'ỏ',
            'õ',
            'ô',
            'ồ',
            'ố',
            'ộ',
            'ổ',
            'ỗ',
            'ơ',
            'ờ',
            'ớ',
            'ợ',
            'ở',
            'ỡ',
            'ù',
            'ú',
            'ụ',
            'ủ',
            'ũ',
            'ư',
            'ừ',
            'ứ',
            'ự',
            'ử',
            'ữ',
            'ỳ',
            'ý',
            'ỵ',
            'ỷ',
            'ỹ',
            'đ'
        ],
        [
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'y',
            'y',
            'y',
            'd'
        ],
        $slug
    );

    // Xóa ký tự đặc biệt
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

    // Thay khoảng trắng thành dấu gạch ngang
    $slug = preg_replace('/\s+/', '-', $slug);

    // Xóa gạch ngang thừa
    $slug = trim($slug, '-');

    return $slug;
}

function removeVietnameseAccents($str) {
    $accents = [
        'a'=>'áàảãạâấầẩẫậăắằẳẵặ',
        'A'=>'ÁÀẢÃẠÂẤẦẨẪẬĂẮẰẲẴẶ',
        'e'=>'éèẻẽẹêếềểễệ',
        'E'=>'ÉÈẺẼẸÊẾỀỂỄỆ',
        'i'=>'íìỉĩị',
        'I'=>'ÍÌỈĨỊ',
        'o'=>'óòỏõọôốồổỗộơớờởỡợ',
        'O'=>'ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢ',
        'u'=>'úùủũụưứừửữự',
        'U'=>'ÚÙỦŨỤƯỨỪỬỮỰ',
        'y'=>'ýỳỷỹỵ',
        'Y'=>'ÝỲỶỸỴ',
        'd'=>'đ',
        'D'=>'Đ'
    ];
    foreach ($accents as $nonAccent => $accentChars) {
        $str = preg_replace("/[" . $accentChars . "]+/u", $nonAccent, $str);
    }
    return $str;
}
