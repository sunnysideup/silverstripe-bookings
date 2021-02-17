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
        <link href="https://fonts.googleapis.com/css2?family=Asap+Condensed:wght@400;500;600;700&family=Asap:wght@400;500;600;700&display=swap" rel="stylesheet">
        <% include WebpackCSSLinks %>
    </head>
    <body class="$BEMClassName<% if $HeroElementIsFirst %> home-page--hero-is-first<% end_if %>">
        <% include Header %>

        <main class="main-content">
            <div class="page-content page-content--has-top-padding">
                <div class="container typography">
                    <div class="stretched-bg-images">
                        $Content
                        $Form
                    </div>
                </div>
            </div>
        </main>

        <% include Footer %>
        <% include MobileMenu %>
        <div id="quick-view-modal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <a href="#" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
                    </div>
                    <div class="modal-body">
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
        <% include WebpackJSLinks %>
        <script src='https://www.youtube.com/iframe_api' async></script>
    </body>
</html>
