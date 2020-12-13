root=/wiz/app/wizserver/web/prod/wapp
index_file=index.html
bak_index_file=index.html.original
file_path=blog-share-script
script_file=share-btn.js

case $1 in

  "install")

    cp -v $root/$index_file $root/$bak_index_file

    mkdir $root/static/$file_path
    cp -vrf ../script/* $root/static/$file_path

    sed -i "s/<\/body>/<script src=\".\/wapp\/static\/$file_path\/share-btn.js\"><\/script><\/body>/g" $root/$index_file

    server_url=`sed '/^serverUrl=/!d;s/.*=//' deploy_config`
    sed -i "s#<serverUrl>#$server_url#g" $root/static/$file_path/$script_file
    ;;

  "uninstall")
    rm -vf $root/$index_file
    cp -v $root/$bak_index_file $root/$index_file
    rm -vf $root/$bak_index_file
    rm -vrf $root/static/$file_path
    ;;

  *)
    echo 'invalid command'
    ;;

esac

