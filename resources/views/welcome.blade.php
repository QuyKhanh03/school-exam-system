<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<p>Xin chào {{ $data['name'] ?? "" }},</p>

<p>Cảm ơn bạn đã quan tâm đến dự án của chúng tôi. Chúng tôi đã nhận được thông tin liên hệ của bạn và sẽ xem xét nó
    trong thời gian sớm nhất. Dưới đây là thông tin bạn đã cung cấp:</p>

<ul>
    <li><strong>Tên:</strong> {{ $data['name'] ?? "" }}</li>
    <li><strong>Email:</strong> {{ $data['email'] ?? "" }}</li>
    <li><strong>Điện thoại:</strong> {{ $data['phone'] ?? "" }}</li>
    <li><strong>Nội dung:</strong> {{ $data['content'] ?? ""}}</li>
</ul>

<p>Chúng tôi sẽ liên hệ lại với bạn sớm nhất. Nếu có bất kỳ câu hỏi hoặc thắc mắc nào, vui lòng liên hệ với chúng tôi
    qua địa chỉ email {{ "cskh.duan@sonha.com" }}.</p>

<p>Cảm ơn bạn một lần nữa và chúc bạn một ngày tốt lành!</p>

<p>Trân trọng,<br>

</body>
</html>
