<!DOCTYPE html>
<html lang="$ContentLocale">
    <head>
        <meta charset="utf-8" />
        <% base_tag %>
        <title>$SiteConfig.Title &mdash; <% if $MetaTitle %>$MetaTitle<% else %>$Title<% end_if %></title>
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="theme-color" content="#c91630"/>
        $MetaTags(false)
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Asap&family=Asap+Condensed:wght@400;500;600;700&display=swap" rel="stylesheet">
        <% include WebpackCSSLinks %>
    </head>
    <body class="$BEMClassName">
        <main>
            $Layout
        </main>
    </body>
</html>
