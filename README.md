# Blog App

![Capture2](https://github.com/user-attachments/assets/d7b27022-45c0-47f7-b1b8-70adfe710f4f)

# Made with: Laravel + React + Tailwind CSS + Inertia

## Introduction

- This is a blog app that I'm going to hook up with my portfolio site. Work in progress!

So currently it shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu, showing the posts for each year. There are archive pages which show all the posts for each year (paginated). RSS Feed component is also included.

Admin can add new posts, edit posts and delete comments.

Blog still needs some work, though, including:

1. Spam filter (reCAPTCHA/honeypot) and rate limiter by IP address
2. Edit and delete comments (own comments only)
3. Setup SEO meta tags
4. An API which is connected to portfolio site (portfolio fetches the latest blog post)
5. Installation of AI translation package (Google/DeepL)
6. Auto-translate on post save + store in DB
7. Language toggle to navbar
8. Toggle loads correct language
9. Anonymous users features (indidual anon numberID:s) and issues (like visible 0 on the UI)
10. A custom API endpoint to fetch the latest action in a component (like the latest blog post and recent comments)
