# Architecture: ps_languageselector

## Purpose

A PrestaShop front-office module that displays a language switcher widget, allowing
customers to select their preferred display language. Updates the entire store UI
to the selected language.

## Directory Structure

```
ps_languageselector.php   # Main module class (WidgetInterface)
views/templates/           # Smarty/Twig templates for language dropdown
translations/              # Translation files
tests/                     # PHPStan and unit tests
```

## Key Design Decisions

Retrieves all active languages from PrestaShop's `Language` model. The language switch
is performed via a form POST that updates the session language and redirects to the
equivalent URL in the new language. Implements `WidgetInterface` for header placement.

## Extension Points

Override template in theme for custom dropdown styling and behavior.
