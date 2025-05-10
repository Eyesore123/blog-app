# Blog App

![Capture2](https://github.com/user-attachments/assets/d7b27022-45c0-47f7-b1b8-70adfe710f4f)


# Made with: Laravel + React + Tailwind CSS + Inertia


## Introduction

- This is a blog app that I'm going to hook up with my portfolio site. Work in progress!

So currently it shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu, showing the posts for each year. There are archive pages which show all the posts for each year (paginated). RSS Feed component is also included. I added custom API endpoint for recent activity, which is used in recent activity component in landing page. I also created "the latest post" endpoint which can be used to fetch the latest post. It's an alternative to rss feed (I plan to use it for my portfolio site).

Admin can add new posts, edit posts and delete comments.

Blog still needs some work, though, including:

1. Spam filter (reCAPTCHA/honeypot) and rate limiter by IP address
2. Edit and delete comments (own comments only)
3. Anonymous users features (indidual anon numberID:s) and issues (like visible 0 on the UI)
4. Setup SEO meta tags
5. User settings page where users can change their password and email, delete their account and sub to newsletters
6. Installation of AI translation package (Google/DeepL)
7. Auto-translate on post save + store in DB
8. Language toggle to navbar
9. Toggle loads correct language
10. Small style fixes for responsive design

![blog3](https://github.com/user-attachments/assets/9b47ad5c-13f9-4858-9291-1eb1d2397d96)
