(function( wp ) {
    const { data, editPost, notices } = wp;

    // Define the custom error message.
    const errorMessage = "Your post must follow the 5-7-5 haiku format. Please adjust the content to contain three lines with 5, 7, and 5 syllables respectively.";

    let wasPublishing = false;

    // Subscribe to changes in the editor state.
    data.subscribe( () => {
        const isSavingPost = data.select( 'core/editor' ).isSavingPost();
        const isPublishingPost = data.select( 'core/editor' ).isPublishingPost();
        const postStatus = data.select( 'core/editor' ).getEditedPostAttribute( 'status' );

        // Track the publishing attempt.
        if ( isPublishingPost && !wasPublishing ) {
            wasPublishing = true;
        }

        // If publish fails and status remains draft, dismiss the "Draft saved" message and show the error.
        if ( wasPublishing && !isSavingPost && postStatus === 'draft' ) {
            wasPublishing = false;

            // Remove any existing snackbar notices, such as "Draft saved."
            const existingNotices = notices.getNotices().filter( notice => notice.type === 'snackbar' );
            existingNotices.forEach( notice => {
                notices.removeNotice( notice.id );
            });

            // Display the custom error notice as a snackbar.
            notices.createNotice(
                'error',
                errorMessage,
                {
                    isDismissible: true,
                    type: 'snackbar'
                }
            );

            // Open the sidebar to show post status.
            editPost.openGeneralSidebar( 'edit-post/document' );
        }

        // Reset the flag if the post publishes successfully.
        if ( postStatus === 'publish' ) {
            wasPublishing = false;
        }
    });
})( window.wp );
