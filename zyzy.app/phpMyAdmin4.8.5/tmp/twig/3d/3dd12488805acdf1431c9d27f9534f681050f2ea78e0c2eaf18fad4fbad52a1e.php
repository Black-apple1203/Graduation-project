<?php

/* login/header.twig */
class __TwigTemplate_d8360d29f657d2fd506e35a97a9b63ae002efecbe5a26ef730be9b29a3d919d0 extends Twig_Template
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
        echo "<div class=\"container\">
<a href=\"";
        // line 2
        echo twig_escape_filter($this->env, PhpMyAdmin\Core::linkURL("https://www.phpmyadmin.net/"), "html", null, true);
        echo "\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"logo\">
<img src=\"";
        // line 3
        echo twig_escape_filter($this->env, $this->getAttribute(($context["theme"] ?? null), "getImgPath", [0 => "logo_right.png", 1 => "pma_logo.png"], "method"), "html", null, true);
        echo "\" id=\"imLogo\" name=\"imLogo\" alt=\"phpMyAdmin\" border=\"0\" />
</a>
<h1>";
        // line 5
        echo sprintf(_gettext("Welcome to %s"), "<bdo dir=\"ltr\" lang=\"en\">phpMyAdmin</bdo>");
        echo "</h1>

<noscript>
";
        // line 8
        echo call_user_func_array($this->env->getFunction('Message_error')->getCallable(), [_gettext("Javascript must be enabled past this point!")]);
        echo "
</noscript>

<div class=\"hide\" id=\"js-https-mismatch\">
";
        // line 12
        echo call_user_func_array($this->env->getFunction('Message_error')->getCallable(), [_gettext("There is mismatch between HTTPS indicated on the server and client. This can lead to non working phpMyAdmin or a security risk. Please fix your server configuration to indicate HTTPS properly.")]);
        echo "
</div>
";
    }

    public function getTemplateName()
    {
        return "login/header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 12,  37 => 8,  31 => 5,  26 => 3,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "login/header.twig", "D:\\Project\\phpstudy_pro\\x64\\Release\\WWW\\phpMyAdmin4.8.5\\templates\\login\\header.twig");
    }
}
