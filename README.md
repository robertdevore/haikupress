# HaikuPress for WordPress®

Enforces a haiku format on post content, allowing only a 5-7-5 syllable structure. 

## Description

HaikuPress is a WordPress® plugin that ensures all your posts adhere to the traditional haiku structure:

- **Three lines**
- **First line:** 5 syllables
- **Second line:** 7 syllables
- **Third line:** 5 syllables

The plugin validates your content before publishing. If the content doesn't meet the haiku criteria, it prevents publishing and provides specific error messages to help you adjust your post.

## Installation

### From WordPress® Dashboard

1. **Download the Plugin:**

    - Clone or download the plugin from the [GitHub repository](https://github.com/robertdevore/haikupress/).
    - Alternatively, get the `haikupress.zip` file directly.
2. **Install the Plugin:**

    - Log in to your WordPress® admin dashboard.
    - Navigate to `Plugins` > `Add New`.
    - Click on `Upload Plugin`.
    - Choose the `haikupress.zip` file you downloaded.
    - Click `Install Now`.
3. **Activate the Plugin:**

    - After installation, click on `Activate Plugin`.

### Manual Installation

1. **Upload via FTP:**

    - Unzip the `haikupress.zip` file.
    - Upload the `haikupress` folder to the `/wp-content/plugins/` directory on your server.
2. **Activate the Plugin:**

    - Log in to your WordPress® admin dashboard.
    - Navigate to `Plugins`.
    - Find `HaikuPress` in the list and click `Activate`.

## Usage

1. **Create a New Post:**

    - Go to `Posts` > `Add New` in your WordPress® dashboard.
2. **Write Your Haiku:**

    - Compose your content following the 5-7-5 syllable structure.
    - Ensure your post contains exactly three lines.
    - Avoid extra whitespace or empty lines between your haiku lines.
3. **Publish Your Post:**

    - Click on `Publish`.
    - If your content meets the haiku requirements, the post will be published.
    - If not, you'll receive an error message indicating what needs to be corrected.

## Error Messages

When your content doesn't meet the haiku criteria, HaikuPress provides clear error messages:

- **Incorrect Line Count:**
    
    Content must contain exactly three lines to be a haiku.

_Occurs when your post doesn't have exactly three non-empty lines._

- **Incorrect Syllable Count:**

    Line X must contain Y syllables: "Your line content here".

_Indicates which line doesn't have the correct number of syllables._

### Example

If the second line of your haiku has 6 syllables instead of 7, you'll see:
    
    Line 2 must contain 7 syllables: "An incorrect line".

## Notes

- **Editor Compatibility:** Works with both the Block Editor (Gutenberg) and the Classic Editor.
- **Syllable Estimation:** The plugin uses a basic algorithm to estimate syllable counts, which may not be 100% accurate due to the complexities of English pronunciation.
- **Error Notifications:** In the Block Editor, error messages appear as dismissible notices. In the Classic Editor, they appear as admin notices after attempting to save or publish.

## License

- **GPL-2.0+**
- See the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.txt) for more details.

## Author

- **Robert DeVore**
- [Website](https://robertdevore.com/)
- [Plugin URI](https://github.com/robertdevore/haikupress/)
* * *

Enjoy writing haikus with HaikuPress!