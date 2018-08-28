#!/bin/bash

export CLIENTID=79742319e39245de5f91d15ff4cac2a8
export CLIENTSECRET=8ad97aceb92c5892e102b093c7c083fa
export TOKEN_EP=https://iam.viessmann.com/idp/v1/token
export AUTHZ_EP=https://iam.viessmann.com/idp/v1/authorize
export SCOPE=openid
export REDIRECTURI=vicare://oauth-callback/everest
export isiwebuserid=
export isiwebpasswd=

CODE=`curl -s -k --user "$isiwebuserid:$isiwebpasswd" "$AUTHZ_EP?client_id=$CLIENTID&scope=$SCOPE&redirect_uri=$REDIRECTURI&response_type=code"| grep -Eio 'code=(.)*"'|  cut -c"6-" | sed 's/.$//'`

echo "CODE=$CODE"
TOKEN=`curl -s -k --user "$CLIENTID:$CLIENTSECRET" -d "code=$CODE&grant_type=authorization_code&client_id=$CLIENTID&redirect_uri=$REDIRECTURI" $TOKEN_EP | sed 's/{"access_token":"//' | sed 's/".*//'`
echo "TOKEN= $TOKEN"
echo ""
echo "access to API"
curl -k  -H "AUTHORIZATION: Bearer $TOKEN" "https://api.viessmann-platform.io/general-management/installations?expanded=true&"  


