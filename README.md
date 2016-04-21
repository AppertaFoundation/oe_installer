OpenEyes
========

Introduction
------------

OpenEyes is a collaborative, open source, project led by Moorfields Eye Hospital. The goal is to produce a framework which will allow the rapid, and continuous development of electronic patient records (EPR) with contributions from Hospitals, Institutions, Academic departments, Companies, and Individuals.

The initial focus is on Ophthalmology, but the design is sufficiently flexible to be used for any clinical specialty.

Ophthalmic units of any size, from a single practitioner to a large eye hospital, should be able to make use of the structure, design, and code to produce a functional, easy to use EPR at minimal cost. By sharing experience, pooling ideas, and distributing development effort, it is expected that the range and capability of OpenEyes will evolve rapidly.

Resources
---------

This is the main repository for development of the core OpenEyes framework.  Event type modules are being developed in other repositories both by ourselves and third party developers.  The [OpenEyes Project Overview](https://github.com/openeyes/OpenEyes/wiki#project-overview) provides a list of currently stable modules.  You may also be interested in our [EyeDraw repository](https://github.com/openeyes/EyeDraw) - this code is used by OpenEyes but may also be used independently.

The principal source of information on OpenEyes is [the OpenEyes website](http://www.openeyes.org.uk)

If you're interested in the OpenEyes project, join our announcements mailing list by sending a blank email to: <announcements+subscribe@openeyes.org.uk>

You can also send general enquiries to our main email address: <info@openeyes.org.uk>

You can find us on twitter at: http://twitter.com/openeyes_oef

Demo versions of OpenEyes featuring fictional patient data for testing purposes are available at: <http://demo.openeyes.org.uk> (u: username p: password).

Developers, developers, developers!
-----------------------------------

Developers can request to join our discussion list for third party developers by sending a blank email to: <dev+subscribe@openeyes.org.uk>

If you need to share repositories with members of the core development team, you can find them listed as _organizational members_ at: <https://github.com/openeyes>

OpenEyes follows the [gitflow](http://nvie.com/posts/a-successful-git-branching-model/) model for git branches. As such, the stable release branch is always on master. For bleeding edge development, use the develop branch.

Setup and installation documentation is available from the README file in the oe_installer repository. 

We are beginning to evolve some documentation for developers on [our github wiki](https://github.com/openeyes/OpenEyes/wiki) including [coding guidelines](https://github.com/openeyes/OpenEyes/wiki/Coding-Guidelines), [working with the core team](https://github.com/openeyes/OpenEyes/wiki/Working-With-The-Core-Team) and our [Event type module development guide](https://github.com/openeyes/OpenEyes/wiki/Event-Type-Module-Development-Guide).

Issues in the core should be logged through the [github issues system](https://github.com/openeyes/OpenEyes/issues) for the moment.  Though we will be making our internal JIRA system available in due course, and will transition logged issues across to this so that we can keep everything in one place  Links for this will follow when this becomes available.

Dev Setup
---------

To begin development, the simplest approach is:

1. clone the repository
1. run vagrant up
1. run devsetup:

    vagrant ssh 
    cd /var/www/protected
    ./yiic devsetup --resetfile=../features/testdata.sql

Printing
--------

OpenEyes now supports full PDF printing using wkhtmltopdf, but it needs to be compiled with a patched QT library in order to work properly. As of version 1.12 a pre-compiled binary
is shipped in the oe_installer repository. However, should you need to re-compile it, you can find instructions for doing this [here](https://github.com/openeyes/OpenEyes/wiki/Compiling-WKHtmlToPDF-to-enable-PDF-printing).
