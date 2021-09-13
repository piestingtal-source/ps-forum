<?php

/**
 * Adds a privacy policy statement.
 */
function psforum_add_privacy_policy_content() {
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
        return;
    }
    $content = '<p class="privacy-policy-tutorial">' . __( 'Einige einleitende Inhalte für den vorgeschlagenen Text.', 'psforum' ) . '</p>'
            . '<strong class="privacy-policy-tutorial">' . __( 'Vorgeschlagener Text:', 'my_plugin_textdomain' ) . '</strong> '
            . sprintf(
                __( 'Wir sammeln Informationen über Dich während der Anmeldung zu unseren Kursen oder beim Auschecken auf unserer Website.

<h3>Was wir sammeln und lagern</h3>

Während Du unsere Webseite besuchst, verfolgen wir Folgendes:

<ul>
    <li>Foren welche Du abonniert, geantwortet oder ein Thema erstellt hast.</li>
    <li>Deine Antworten und Themen.</li>
</ul>

Wenn Du bei uns diskutierst, wirst Du gebeten, Informationen wie Deinen Namen, Email-Adresse und optionale Kontoinformationen wie Benutzername und Passwort anzugeben. Wir verwenden diese Informationen, um
<ul>
	<li>Senden von Informationen zu Deinem Foren-Abos und Deiner Forenaktivität.</li>
	<li>Reagieren auf Deine Anfragen, einschließlich Meldungen und Beschwerden.</li>
	<li>Zum verhindern von Betrug.</li>
</ul>

Wenn Du ein Konto erstellst, speichern wir Deinen Namen, Adresse, Email-Adresse und weitere Details für Deinen Account.

Wir speichern auch Kommentare oder Bewertungen, wenn Du diese hinterlassen möchtest.

<h3>Wer in unserem Team hat Zugriff</h3>

Mitglieder unseres Teams haben Zugriff auf die Informationen, die Du uns zur Verfügung stellst. Administratoren, Ausbilder und Moderatoren können auf Folgendes zugreifen:
<ul>
	<li>Benutzerinformationen wie Name, Email-Adresse und Forenaktivitäten.</li>
</ul>

Unsere Teammitglieder haben Zugriff auf diese Informationen, um Meldungen zu verifizeren und Dich zu unterstützen.', 'psforum' ),
            );
    wp_add_privacy_policy_content( 'psforum', wp_kses_post( wpautop( $content, false ) ) );
}
 
add_action( 'admin_init', 'psforum_add_privacy_policy_content' );