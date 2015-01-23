# Contact

Contact is simple API for submitting e-mail messages. It's useful for cases when the only dynamic thing on static site is contact form.

## Requirements

Contact is based on [Slim Framework](http://www.slimframework.com). All dependencies are managed using Composer, so you need to have it installed in order to download them. Just run:

```
$ composer install
```

## Configuration

To change default validation messages, field labels, recipient email addres etc., just modify `config.json` file.

## Usage

If submitted data is valid:

```
$ curl -i http://contact/?name=Test+Tosteron&sender=test@tosteron.com&message=Hello!
```
```json
Status: HTTP/1.1 201 Created

{
  "message": "Thank you for your feedback!",
  "errors": []
}
```

If there's one or more errors:

```
$ curl -i http://contact/?name=&sender=&message=
```
```json
Status: HTTP/1.1 400 Bad Request

{
  "message":"Please check errors below each field.",
  "errors": {
    "sender": "Sender should not be blank.",
    "name": "Name should not be blank.",
    "message": "Message should not be blank."
  }
}
```

### Example

Below is example JavaScript code showing how API can be used.

```javascript
$('.contact-form').on('submit', function(event) {
  var $form = $(this);

  $.ajax({
    url: $form.attr('action'),
    data: $form.serialize()
  })
  .always(function() {
    $('.success, .error', $form).remove();
  })
  .done(function(data) {
    $form.get().reset();

    $('<div>')
      .addClass('success')
      .text(data.message)
      .prependTo($form);
  })
  .fail(function(xhr) {
    var data = xhr.responseJSON;

    $.each(data.errors, function(field, error) {
      $('[name="' + field + '"]', $form).after($('<p>').addClass('error').text(error));
    });
  });

  event.preventDefault();
});
```
