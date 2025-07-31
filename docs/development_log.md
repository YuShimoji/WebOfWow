# Development Log

## 2024-07-30

- Initialized the WordPress theme project.
- Set up basic theme files: `index.php`, `header.php`, `footer.php`, `style.css`, `functions.php`.
- Corrected theme installation issues by moving `style.css` to the root and enqueueing it properly in `functions.php`.
- Initialized Git repository and pushed to GitHub.
- Created documentation structure.

## 2024-07-31

- Added "AI Post Generator" menu to the WordPress admin panel.
- Implemented a basic form to generate a test post from a given topic.
- Created `inc/ai-functions.php` to house AI-related functionalities.
- Integrated OpenAI API to generate post content based on a topic.
- Added a settings page under "AI Post Generator" to save the OpenAI API key.
- Implemented RSS feed fetching functionality to gather topics from external sites.
- Displayed fetched RSS feed items on the AI Post Generator page, allowing users to select them as topics.
- Implemented an auto-linking feature that automatically creates links between posts based on their titles when a post is published or updated.