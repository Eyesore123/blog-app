# Blog App

![Capture2](https://github.com/user-attachments/assets/d7b27022-45c0-47f7-b1b8-70adfe710f4f)

# Made with: Laravel + React + Tailwind CSS + Inertia

## Introduction

- This is a blog app that I'm going to hook up with my portfolio site. Work in progress!

So currently it shows posts and comments. Posts can be searched by title and topic. Posts also can be searched using the dropdown menu, showing the posts for each year. There are archive pages which show all the posts for each year (paginated). RSS Feed component is also included. I added custom API endpoint for recent activity, which is used in recent activity component in landing page. I also created "the latest post" endpoint which can be used to fetch the latest post. It's an alternative to rss feed (I plan to use it for my portfolio site).

Admin can add new posts, edit posts and delete comments. Now admin can also deactivate and - as an ultimate solution - delete accounts. Logged in users can add comments, edit their comments and delete comments when there are no replies. Comments are rate limited by IP address (10 comments per day), and there are no Captchas because IP address guarantees that the limiter applies to many users from the same IP address. Rate limiter is done using custom RateLimitService class. Likewise SEO is done using a custom SEO class and then provided for the app using react-helmet-async package (it was the easiest solution considering I'm not using blade directives).

Logged in users have a My Account page where they can change their password, delete their account and subscribe to a newsletter.

Providers are used for themes, alerts and confirmations.

Blog still needs some work, though, including:

1. Installation of AI translation package (Google/DeepL)?
2. Auto-translate on post save + store in DB
3. Language toggle to navbar
4. Toggle button in navbar loads correct language
5. Forgot password section to login page
6. Advanced features for admin (preview for the post, image size adjustments? etc.)
7. Tag filtering
8. Markdown editor
9. Email subscription (latest blog post in newsletter) backend and admin panel
10. Blog posts get automatically uploaded to LinkedIn
11. Loading spinners for images

![blog3](https://github.com/user-attachments/assets/9b47ad5c-13f9-4858-9291-1eb1d2397d96)
