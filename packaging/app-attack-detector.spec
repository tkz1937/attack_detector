
Name: app-attack-detector
Epoch: 1
Version: 2.3.1
Release: 1%{dist}
Summary: Attack Detector
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-network
Requires: app-ssh-server

%description
Attack Detector scans your system for authentication failures across various types of services installed on your system.  If the failure threshold is reached, the app will block the attacking system.

%package core
Summary: Attack Detector - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-events-core
Requires: app-network-core
Requires: fail2ban-server
Requires: ipset

%description core
Attack Detector scans your system for authentication failures across various types of services installed on your system.  If the failure threshold is reached, the app will block the attacking system.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/attack_detector
cp -r * %{buildroot}/usr/clearos/apps/attack_detector/

install -d -m 0755 %{buildroot}/var/clearos/attack_detector
install -d -m 0755 %{buildroot}/var/clearos/attack_detector/filters
install -d -m 0700 %{buildroot}/var/clearos/attack_detector/run
install -d -m 0755 %{buildroot}/var/clearos/attack_detector/state
install -D -m 0755 packaging/90-attack-detector %{buildroot}/etc/clearos/firewall.d/90-attack-detector
install -D -m 0440 packaging/app-attack-detector.sudoers %{buildroot}/etc/sudoers.d/app-attack-detector
install -D -m 0644 packaging/fail2ban.php %{buildroot}/var/clearos/base/daemon/fail2ban.php

%post
logger -p local6.notice -t installer 'app-attack-detector - installing'

%post core
logger -p local6.notice -t installer 'app-attack-detector-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/attack_detector/deploy/install ] && /usr/clearos/apps/attack_detector/deploy/install
fi

[ -x /usr/clearos/apps/attack_detector/deploy/upgrade ] && /usr/clearos/apps/attack_detector/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-attack-detector - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-attack-detector-core - uninstalling'
    [ -x /usr/clearos/apps/attack_detector/deploy/uninstall ] && /usr/clearos/apps/attack_detector/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/attack_detector/controllers
/usr/clearos/apps/attack_detector/htdocs
/usr/clearos/apps/attack_detector/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/attack_detector/packaging
%exclude /usr/clearos/apps/attack_detector/unify.json
%dir /usr/clearos/apps/attack_detector
%dir /var/clearos/attack_detector
%dir /var/clearos/attack_detector/filters
%dir /var/clearos/attack_detector/run
%dir /var/clearos/attack_detector/state
/usr/clearos/apps/attack_detector/deploy
/usr/clearos/apps/attack_detector/language
/usr/clearos/apps/attack_detector/libraries
/etc/clearos/firewall.d/90-attack-detector
/etc/sudoers.d/app-attack-detector
/var/clearos/base/daemon/fail2ban.php
