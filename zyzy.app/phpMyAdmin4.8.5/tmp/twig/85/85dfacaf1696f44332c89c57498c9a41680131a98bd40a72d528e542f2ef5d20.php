<?php

/* navigation/logo.twig */
class __TwigTemplate_0ccd5299585ae6f9412ba80bd4b9b360772bf2840f1d29ee5a12b91bc17baa43 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        if (($context["display_logo"] ?? null)) {
            // line 2
            echo "    <div id=\"pmalogo\">
        ";
            // line 3
            if (($context["use_logo_link"] ?? null)) {
                // line 4
                echo "            <a href=\"";
                echo (((isset($context["logo_link"]) || array_key_exists("logo_link", $context))) ? (_twig_default_filter(($context["logo_link"] ?? null), "#")) : ("#"));
                echo "\"";
                // line 5
                echo twig_escape_filter($this->env, (((isset($context["link_attribs"]) || array_key_exists("link_attribs", $context))) ? ((" " . ($context["link_attribs"] ?? null))) : ("")), "html", null, true);
                echo ">
        ";
            }
            // line 7
            echo "        ";
            echo ($context["logo"] ?? null);
            echo "
        ";
            // line 8
            if (($context["use_logo_link"] ?? null)) {
                // line 9
                echo "            </a>
        ";
            }
            // line 11
            echo "    </div>
";
        }
    }

    public function getTemplateName()
    {
        return "navigation/logo.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  46 => 11,  42 => 9,  40 => 8,  35 => 7,  30 => 5,  26 => 4,  24 => 3,  21 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "navigation/logo.twig", "D:\\Project\\phpstudy_pro\\x64\\Release\\WWW\\phpMyAdmin4.8.5\\templates\\navigation\\logo.twig");
    }
}
