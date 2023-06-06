<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* header.twig */
class __TwigTemplate_bd998527ca34e40044790afae8e9e655 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!doctype html>
<html lang=\"";
        // line 2
        echo twig_escape_filter($this->env, ($context["lang"] ?? null), "html", null, true);
        echo "\" dir=\"";
        echo twig_escape_filter($this->env, ($context["text_dir"] ?? null), "html", null, true);
        echo "\">
<head>
  <meta charset=\"utf-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <meta name=\"referrer\" content=\"no-referrer\">
  <meta name=\"robots\" content=\"noindex,nofollow,notranslate\">
  <meta name=\"google\" content=\"notranslate\">
  ";
        // line 9
        if ( !($context["allow_third_party_framing"] ?? null)) {
            // line 10
            echo "<style id=\"cfs-style\">html{display: none;}</style>";
        }
        // line 12
        echo "
  <link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">
  <link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\">
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 15
        echo twig_escape_filter($this->env, ($context["theme_path"] ?? null), "html", null, true);
        echo "/jquery/jquery-ui.css\">
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 16
        echo twig_escape_filter($this->env, ($context["base_dir"] ?? null), "html", null, true);
        echo "js/vendor/codemirror/lib/codemirror.css?";
        echo twig_escape_filter($this->env, ($context["version"] ?? null), "html", null, true);
        echo "\">
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 17
        echo twig_escape_filter($this->env, ($context["base_dir"] ?? null), "html", null, true);
        echo "js/vendor/codemirror/addon/hint/show-hint.css?";
        echo twig_escape_filter($this->env, ($context["version"] ?? null), "html", null, true);
        echo "\">
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 18
        echo twig_escape_filter($this->env, ($context["base_dir"] ?? null), "html", null, true);
        echo "js/vendor/codemirror/addon/lint/lint.css?";
        echo twig_escape_filter($this->env, ($context["version"] ?? null), "html", null, true);
        echo "\">
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 19
        echo twig_escape_filter($this->env, ($context["theme_path"] ?? null), "html", null, true);
        echo "/css/theme";
        echo (((($context["text_dir"] ?? null) == "rtl")) ? (".rtl") : (""));
        echo ".css?";
        echo twig_escape_filter($this->env, ($context["version"] ?? null), "html", null, true);
        echo "\">
  <title>";
        // line 20
        echo twig_escape_filter($this->env, ($context["title"] ?? null), "html", null, true);
        echo "</title>
  ";
        // line 21
        echo ($context["scripts"] ?? null);
        echo "
  <noscript><style>html{display:block}</style></noscript>
</head>
<body";
        // line 24
        (( !twig_test_empty(($context["body_id"] ?? null))) ? (print (twig_escape_filter($this->env, (" id=" . ($context["body_id"] ?? null)), "html", null, true))) : (print ("")));
        echo ">
  ";
        // line 25
        echo ($context["navigation"] ?? null);
        echo "
  ";
        // line 26
        echo ($context["custom_header"] ?? null);
        echo "
  ";
        // line 27
        echo ($context["load_user_preferences"] ?? null);
        echo "

  ";
        // line 29
        if ( !($context["show_hint"] ?? null)) {
            // line 30
            echo "    <span id=\"no_hint\" class=\"hide\"></span>
  ";
        }
        // line 32
        echo "
  ";
        // line 33
        if (($context["is_warnings_enabled"] ?? null)) {
            // line 34
            echo "    <noscript>
      ";
            // line 35
            echo $this->env->getFilter('error')->getCallable()(_gettext("Javascript must be enabled past this point!"));
            echo "
    </noscript>
  ";
        }
        // line 38
        echo "
  ";
        // line 39
        if ((($context["is_menu_enabled"] ?? null) && (($context["server"] ?? null) > 0))) {
            // line 40
            echo "    ";
            echo ($context["menu"] ?? null);
            echo "
    <span id=\"page_nav_icons\" class=\"d-print-none\">
      <span id=\"lock_page_icon\"></span>
      <span id=\"page_settings_icon\">
        ";
            // line 44
            echo PhpMyAdmin\Html\Generator::getImage("s_cog", _gettext("Page-related settings"));
            echo "
      </span>
      <a id=\"goto_pagetop\" href=\"#\">";
            // line 46
            echo PhpMyAdmin\Html\Generator::getImage("s_top", _gettext("Click on the bar to scroll to top of page"));
            echo "</a>
    </span>
  ";
        }
        // line 49
        echo "
  ";
        // line 50
        echo ($context["console"] ?? null);
        echo "

  <div id=\"page_content\">
    ";
        // line 53
        echo ($context["messages"] ?? null);
        echo "

    ";
        // line 55
        echo ($context["recent_table"] ?? null);
        // line 56
        if (($context["is_logged_in"] ?? null)) {
            // line 57
            echo twig_include($this->env, $context, "modals/preview_sql_modal.twig");
            echo "
    ";
            // line 58
            echo twig_include($this->env, $context, "modals/enum_set_editor.twig");
            echo "
    ";
            // line 59
            echo twig_include($this->env, $context, "modals/create_view.twig");
        }
    }

    public function getTemplateName()
    {
        return "header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  189 => 59,  185 => 58,  181 => 57,  179 => 56,  177 => 55,  172 => 53,  166 => 50,  163 => 49,  157 => 46,  152 => 44,  144 => 40,  142 => 39,  139 => 38,  133 => 35,  130 => 34,  128 => 33,  125 => 32,  121 => 30,  119 => 29,  114 => 27,  110 => 26,  106 => 25,  102 => 24,  96 => 21,  92 => 20,  84 => 19,  78 => 18,  72 => 17,  66 => 16,  62 => 15,  57 => 12,  54 => 10,  52 => 9,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "header.twig", "/Users/mac/Desktop/laravel-projects/cipi/public/mysecureadmin/templates/header.twig");
    }
}
