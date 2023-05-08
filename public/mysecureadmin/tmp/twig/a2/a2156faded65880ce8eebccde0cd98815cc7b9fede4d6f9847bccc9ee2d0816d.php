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

/* footer.twig */
class __TwigTemplate_45423b8055087c3413e3b44907c01adcc137188d685c441dbe5eeb35e74c6bd1 extends Template
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
        if ( !($context["is_ajax"] ?? null)) {
            // line 2
            echo "  </div>
";
        }
        // line 4
        if (( !($context["is_ajax"] ?? null) &&  !($context["is_minimal"] ?? null))) {
            // line 5
            echo "  ";
            if ( !twig_test_empty(($context["self_url"] ?? null))) {
                // line 6
                echo "    <div id=\"selflink\" class=\"d-print-none\">
      <a href=\"";
                // line 7
                echo twig_escape_filter($this->env, ($context["self_url"] ?? null), "html", null, true);
                echo "\" title=\"";
echo _gettext("Open new phpMyAdmin window");
                echo "\" target=\"_blank\" rel=\"noopener noreferrer\">
        ";
                // line 8
                if (PhpMyAdmin\Util::showIcons("TabsMode")) {
                    // line 9
                    echo "          ";
                    echo PhpMyAdmin\Html\Generator::getImage("window-new", _gettext("Open new phpMyAdmin window"));
                    echo "
        ";
                } else {
                    // line 11
                    echo "          ";
echo _gettext("Open new phpMyAdmin window");
                    // line 12
                    echo "        ";
                }
                // line 13
                echo "      </a>
    </div>
  ";
            }
            // line 16
            echo "
  <div class=\"clearfloat d-print-none\" id=\"pma_errors\">
    ";
            // line 18
            echo ($context["error_messages"] ?? null);
            echo "
  </div>

  ";
            // line 21
            echo ($context["scripts"] ?? null);
            echo "

  ";
            // line 23
            if (($context["is_demo"] ?? null)) {
                // line 24
                echo "    <div id=\"pma_demo\" class=\"d-print-none\">
      ";
                // line 25
                ob_start(function () { return ''; });
                // line 26
                echo "        <a href=\"";
                echo PhpMyAdmin\Url::getFromRoute("/");
                echo "\">";
echo _gettext("phpMyAdmin Demo Server");
                echo ":</a>
        ";
                // line 27
                if ( !twig_test_empty(($context["git_revision_info"] ?? null))) {
                    // line 28
                    echo "          ";
                    ob_start(function () { return ''; });
                    // line 29
                    echo "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"";
                    echo twig_escape_filter($this->env, PhpMyAdmin\Core::linkURL(twig_get_attribute($this->env, $this->source, ($context["git_revision_info"] ?? null), "revisionUrl", [], "any", false, false, false, 29)), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["git_revision_info"] ?? null), "revision", [], "any", false, false, false, 29), "html", null, true);
                    echo "</a>";
                    $context["revision_info"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
                    // line 31
                    echo "          ";
                    ob_start(function () { return ''; });
                    // line 32
                    echo "<a target=\"_blank\" rel=\"noopener noreferrer\" href=\"";
                    echo twig_escape_filter($this->env, PhpMyAdmin\Core::linkURL(twig_get_attribute($this->env, $this->source, ($context["git_revision_info"] ?? null), "branchUrl", [], "any", false, false, false, 32)), "html", null, true);
                    echo "\">";
                    echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["git_revision_info"] ?? null), "branch", [], "any", false, false, false, 32), "html", null, true);
                    echo "</a>";
                    $context["branch_info"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
                    // line 34
                    echo "          ";
                    echo twig_sprintf(_gettext("Currently running Git revision %1\$s from the %2\$s branch."), ($context["revision_info"] ?? null), ($context["branch_info"] ?? null));
                    echo "
        ";
                } else {
                    // line 36
                    echo "          ";
echo _gettext("Git information missing!");
                    // line 37
                    echo "        ";
                }
                // line 38
                echo "      ";
                $___internal_parse_34_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 25
                echo $this->env->getFilter('notice')->getCallable()($___internal_parse_34_);
                // line 39
                echo "    </div>
  ";
            }
            // line 41
            echo "
  ";
            // line 42
            echo ($context["footer"] ?? null);
            echo "
";
        }
        // line 44
        if ( !($context["is_ajax"] ?? null)) {
            // line 45
            echo "  </body>
</html>
";
        }
    }

    public function getTemplateName()
    {
        return "footer.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  158 => 45,  156 => 44,  151 => 42,  148 => 41,  144 => 39,  142 => 25,  139 => 38,  136 => 37,  133 => 36,  127 => 34,  120 => 32,  117 => 31,  110 => 29,  107 => 28,  105 => 27,  98 => 26,  96 => 25,  93 => 24,  91 => 23,  86 => 21,  80 => 18,  76 => 16,  71 => 13,  68 => 12,  65 => 11,  59 => 9,  57 => 8,  51 => 7,  48 => 6,  45 => 5,  43 => 4,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "footer.twig", "C:\\Users\\OGOCHUKWUEBUKA\\Desktop\\laravel-projects\\cipi\\public\\mysecureadmin\\templates\\footer.twig");
    }
}
