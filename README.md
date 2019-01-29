Libero browser
==============

[![Build Status](https://travis-ci.com/libero/browser.svg?branch=master)](https://travis-ci.com/libero/browser)

Serve content read from a Libero API to the public.

Getting started
---------------

To run a website reading from two content services (`blog-articles` and `scholarly-articles`):

1. Create `config/packages/content_page.yaml`:

    ```yaml
    content_page:
        pages:
            blog_article:
                handler: 'libero'
                path: '/blog/{id}'
                service: 'blog-articles'
            scholarly_article:
                handler: 'libero'
                path: '/articles/{id}'
                service: 'scholarly-articles'
    ```

2. Run `docker-compose -f docker-compose.yaml down --v && docker-compose -f docker-compose.yaml up --build`.

    Note the `dev` environment is already preconfigured and can be accessed be running `docker-compose down -v && docker-compose up --build` instead. 

3. Open http://localhost:8080/blog/foo to see the blog post `foo`.

Getting help
------------

- Report a bug or request a feature on [GitHub](https://github.com/libero/libero/issues/new/choose).
- Ask a question on the [Libero Community Slack](https://libero-community.slack.com/).
- Read the [code of conduct](https://libero.pub/code-of-conduct).
