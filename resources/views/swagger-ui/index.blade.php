<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta
    name="description"
    content="SwaggerUI"
  />
  <title>SwaggerUI</title>
  <link rel="stylesheet" href="{{ asset('css/swagger-ui.css')}}" />
  <link rel="icon" type="image/png" href="{{ asset('images/favicon-32x32.png')}}" sizes="32x32" />
  <link rel="icon" type="image/png" href="{{ asset('images/favicon-16x16.png')}}" sizes="16x16" />
  <style>
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body {
      margin:0;
      background: #fafafa;
    }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="{{ asset('js/swagger-ui-bundle.js')}}" crossorigin></script>
<script src="{{ asset('js/swagger-ui-standalone-preset.js')}}" crossorigin></script>
<script>
  window.onload = () => {
    
    window.ui = SwaggerUIBundle({
      urls: [
        {url: window.location.origin+'/docs/api-administration.yaml', name: "Administration"},
        {url: window.location.origin+'/docs/api-configuracoes.yaml', name: "Configuracoes"},
      ],
      dom_id: '#swagger-ui',
      presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
      ],
      layout: "StandaloneLayout",
      docExpansion : "none",
      filter: true,
      oauth2RedirectUrl: window.location.origin+'/documentation/redirect',
    });

    window.ui.initOAuth({
      clientId: "documental-client"
    });

  };
</script>
</body>
</html>