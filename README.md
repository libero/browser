Libero browser
==============

[![Build Status](https://travis-ci.com/libero/browser.svg?branch=master)](https://travis-ci.com/libero/browser)

Serve content read from a Libero API to the public.

Getting started
---------------

1. Run `docker-compose down --volumes --remove-orphans && docker-compose up --build`.

2. Open http://localhost:8080/articles/article1 to see the scholarly article `article1` in the `dev` environment.

### Configuration

The `dev` environment reads content from a [Libero dummy API](https://github.com/libero/dummy-api).

Running in production
---------------------

The application is not yet stable, but Docker images are published ([`liberoadmin/browser`](https://hub.docker.com/r/liberoadmin/browser)).

To run an image reading from two content services (`blog-articles` and `scholarly-articles`), you will need the following configuration:

- Set the `API_URI` environment variable to be the root of the Libero API.

- Create `config/packages/content_page.yaml` (either by extending the image or mounting a file):

    ```yaml
    content_page:
        pages:
            blog_article:
                path: '/blog/{id}'
                service: 'blog-articles'
            scholarly_article:
                path: '/articles/{id}'
                service: 'scholarly-articles'
    ```

Getting help
------------

- Report a bug or request a feature on [GitHub](https://github.com/libero/libero/issues/new/choose).
- Ask a question on the [Libero Community Slack](https://libero.pub/join-slack).
- Read the [code of conduct](https://libero.pub/code-of-conduct).
