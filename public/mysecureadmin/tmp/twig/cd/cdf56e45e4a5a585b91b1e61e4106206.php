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

/* login/form.twig */
class __TwigTemplate_bb3813f773eed68dc2bd498c864998f0 extends Template
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
        echo ($context["login_header"] ?? null);
        echo "

";
        // line 3
        if (($context["is_demo"] ?? null)) {
            // line 4
            echo "  <div class=\"card mb-4\">
    <div class=\"card-header\">";
echo _gettext("phpMyAdmin Demo Server");
            // line 5
            echo "</div>
    <div class=\"card-body\">
      ";
            // line 7
            ob_start(function () { return ''; });
            // line 8
            echo "        ";
echo _gettext("You are using the demo server. You can do anything here, but please do not change root, debian-sys-maint and pma users. More information is available at %s.");
            // line 11
            echo "      ";
            $___internal_parse_0_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 7
            echo twig_sprintf($___internal_parse_0_, "<a href=\"url.php?url=https://demo.phpmyadmin.net/\" target=\"_blank\" rel=\"noopener noreferrer\">demo.phpmyadmin.net</a>");
            // line 12
            echo "    </div>
  </div>
";
        }
        // line 15
        echo "
";
        // line 16
        echo ($context["error_messages"] ?? null);
        echo "

";
        // line 18
        if ( !twig_test_empty(($context["available_languages"] ?? null))) {
            // line 19
            echo "  <div class='hide js-show'>
    <div class=\"card mb-4\">
      <div class=\"card-header\">
        <span id=\"languageSelectLabel\">
          ";
echo _gettext("Language");
            // line 24
            echo "          ";
            if ((_gettext("Language") != "Language")) {
                // line 25
                echo "            ";
                // line 27
                echo "            <i lang=\"en\" dir=\"ltr\">(Language)</i>
          ";
            }
            // line 29
            echo "        </span>
      </div>
      <div class=\"card-body\">
        <form method=\"get\" action=\"";
            // line 32
            echo PhpMyAdmin\Url::getFromRoute("/");
            echo "\" class=\"disableAjax\">
          ";
            // line 33
            echo PhpMyAdmin\Url::getHiddenInputs(($context["form_params"] ?? null));
            echo "
          <select name=\"lang\" class=\"form-select autosubmit\" lang=\"en\" dir=\"ltr\" id=\"languageSelect\" aria-labelledby=\"languageSelectLabel\">
            ";
            // line 35
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["available_languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 36
                echo "              <option value=\"";
                echo twig_escape_filter($this->env, twig_lower_filter($this->env, twig_get_attribute($this->env, $this->source, $context["language"], "getCode", [], "method", false, false, false, 36)), "html", null, true);
                echo "\"";
                echo ((twig_get_attribute($this->env, $this->source, $context["language"], "isActive", [], "method", false, false, false, 36)) ? (" selected") : (""));
                echo ">";
                // line 37
                echo twig_get_attribute($this->env, $this->source, $context["language"], "getName", [], "method", false, false, false, 37);
                // line 38
                echo "</option>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['language'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 40
            echo "          </select>
        </form>
      </div>
    </div>
  </div>
";
        }
        // line 46
        echo "
<form method=\"post\" id=\"login_form\" action=\"index.php?route=/\" name=\"login_form\" class=\"";
        // line 48
        echo (( !($context["is_session_expired"] ?? null)) ? ("disableAjax hide ") : (""));
        echo "js-show\"";
        echo (( !($context["has_autocomplete"] ?? null)) ? (" autocomplete=\"off\"") : (""));
        echo ">
  ";
        // line 50
        echo "  ";
        echo PhpMyAdmin\Url::getHiddenInputs(($context["form_params"] ?? null), "", 0, "server");
        echo "
  <input type=\"hidden\" name=\"set_session\" value=\"";
        // line 51
        echo twig_escape_filter($this->env, ($context["session_id"] ?? null), "html", null, true);
        echo "\">
  ";
        // line 52
        if (($context["is_session_expired"] ?? null)) {
            // line 53
            echo "    <input type=\"hidden\" name=\"session_timedout\" value=\"1\">
  ";
        }
        // line 55
        echo "
  <div class=\"card mb-4\">
    <div class=\"card-header\">
      ";
echo _gettext("Log in");
        // line 59
        echo "      ";
        echo PhpMyAdmin\Html\MySQLDocumentation::showDocumentation("index");
        echo "
    </div>
    <div class=\"card-body\">
      ";
        // line 62
        if (($context["is_arbitrary_server_allowed"] ?? null)) {
            // line 63
            echo "        <div class=\"row mb-3\">
          <label for=\"serverNameInput\" class=\"col-sm-4 col-form-label\" title=\"";
echo _gettext("You can enter hostname/IP address and port separated by space.");
            // line 64
            echo "\">
            ";
echo _gettext("Server:");
            // line 66
            echo "          </label>
          <div class=\"col-sm-8\">
            <input type=\"text\" name=\"pma_servername\" id=\"serverNameInput\" value=\"";
            // line 68
            echo twig_escape_filter($this->env, ($context["default_server"] ?? null), "html", null, true);
            echo "\" class=\"form-control\" title=\"";
echo _gettext("You can enter hostname/IP address and port separated by space.");
            // line 69
            echo "\">
          </div>
        </div>
      ";
        }
        // line 73
        echo "
      <div class=\"row mb-3\">
        <label for=\"input_username\" class=\"col-sm-4 col-form-label\">
          ";
echo _gettext("Username:");
        // line 77
        echo "        </label>
        <div class=\"col-sm-8\">
          <input type=\"text\" name=\"pma_username\" id=\"input_username\" value=\"";
        // line 79
        echo twig_escape_filter($this->env, ($context["default_user"] ?? null), "html", null, true);
        echo "\" class=\"form-control\" autocomplete=\"username\" spellcheck=\"false\">
        </div>
      </div>

      <div class=\"row\">
        <label for=\"input_password\" class=\"col-sm-4 col-form-label\">
          ";
echo _gettext("Password:");
        // line 86
        echo "        </label>
        <div class=\"col-sm-8\">
          <input type=\"password\" name=\"pma_password\" id=\"input_password\" value=\"\" class=\"form-control\" autocomplete=\"current-password\" spellcheck=\"false\">
        </div>
      </div>

      ";
        // line 92
        if (($context["has_servers"] ?? null)) {
            // line 93
            echo "        <div class=\"row mt-3\">
          <label for=\"select_server\" class=\"col-sm-4 col-form-label\">
            ";
echo _gettext("Server choice:");
            // line 96
            echo "          </label>
          <div class=\"col-sm-8\">
            <select name=\"server\" id=\"select_server\" class=\"form-select\"";
            // line 99
            if (($context["is_arbitrary_server_allowed"] ?? null)) {
                echo " onchange=\"document.forms['login_form'].elements['pma_servername'].value = ''\"";
            }
            echo ">
              ";
            // line 100
            echo ($context["server_options"] ?? null);
            echo "
            </select>
          </div>
        </div>
      ";
        } else {
            // line 105
            echo "        <input type=\"hidden\" name=\"server\" value=\"";
            echo twig_escape_filter($this->env, ($context["server"] ?? null), "html", null, true);
            echo "\">
      ";
        }
        // line 107
        echo "    </div>
    <div class=\"card-footer\">
      ";
        // line 109
        if (($context["has_captcha"] ?? null)) {
            // line 110
            echo "        <script src=\"";
            echo twig_escape_filter($this->env, ($context["captcha_api"] ?? null), "html", null, true);
            echo "?hl=";
            echo twig_escape_filter($this->env, ($context["lang"] ?? null), "html", null, true);
            echo "\" async defer></script>
        ";
            // line 111
            if (($context["use_captcha_checkbox"] ?? null)) {
                // line 112
                echo "          <div class=\"row g-3\">
            <div class=\"col\">
              <div class=\"";
                // line 114
                echo twig_escape_filter($this->env, ($context["captcha_req"] ?? null), "html", null, true);
                echo "\" data-sitekey=\"";
                echo twig_escape_filter($this->env, ($context["captcha_key"] ?? null), "html", null, true);
                echo "\"></div>
            </div>
            <div class=\"col align-self-center text-end\">
              <input class=\"btn btn-primary\" value=\"";
echo _gettext("Log in");
                // line 117
                echo "\" type=\"submit\" id=\"input_go\">
            </div>
          </div>
        ";
            } else {
                // line 121
                echo "          <input class=\"btn btn-primary ";
                echo twig_escape_filter($this->env, ($context["captcha_req"] ?? null), "html", null, true);
                echo "\" data-sitekey=\"";
                echo twig_escape_filter($this->env, ($context["captcha_key"] ?? null), "html", null, true);
                echo "\" data-callback=\"Functions_recaptchaCallback\" value=\"";
echo _gettext("Log in");
                echo "\" type=\"submit\" id=\"input_go\">
        ";
            }
            // line 123
            echo "      ";
        } else {
            // line 124
            echo "        <input class=\"btn btn-primary\" value=\"";
echo _gettext("Log in");
            echo "\" type=\"submit\" id=\"input_go\">
      ";
        }
        // line 126
        echo "    </div>
  </div>
</form>

";
        // line 130
        if ( !twig_test_empty(($context["errors"] ?? null))) {
            // line 131
            echo "  <div id=\"pma_errors\">
    ";
            // line 132
            echo ($context["errors"] ?? null);
            echo "
  </div>
  </div>
  </div>
";
        }
        // line 137
        echo "
";
        // line 138
        echo ($context["login_footer"] ?? null);
        echo "

";
        // line 140
        echo ($context["config_footer"] ?? null);
        echo "
";
    }

    public function getTemplateName()
    {
        return "login/form.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  334 => 140,  329 => 138,  326 => 137,  318 => 132,  315 => 131,  313 => 130,  307 => 126,  301 => 124,  298 => 123,  288 => 121,  282 => 117,  273 => 114,  269 => 112,  267 => 111,  260 => 110,  258 => 109,  254 => 107,  248 => 105,  240 => 100,  234 => 99,  230 => 96,  225 => 93,  223 => 92,  215 => 86,  205 => 79,  201 => 77,  195 => 73,  189 => 69,  185 => 68,  181 => 66,  177 => 64,  173 => 63,  171 => 62,  164 => 59,  158 => 55,  154 => 53,  152 => 52,  148 => 51,  143 => 50,  137 => 48,  134 => 46,  126 => 40,  119 => 38,  117 => 37,  111 => 36,  107 => 35,  102 => 33,  98 => 32,  93 => 29,  89 => 27,  87 => 25,  84 => 24,  77 => 19,  75 => 18,  70 => 16,  67 => 15,  62 => 12,  60 => 7,  57 => 11,  54 => 8,  52 => 7,  48 => 5,  44 => 4,  42 => 3,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "login/form.twig", "/Users/mac/Desktop/laravel-projects/cipi/public/mysecureadmin/templates/login/form.twig");
    }
}
