# Обработка правил валидации ветвления =====

GITLAB_RULES_HANDLER=./.git/hooks/prepare-commit-msg
GITLAB_RULES_HANDLER_SAMPLE="${GITLAB_RULES_HANDLER}.sample"

add-commit-msg-rule:
	@if [ -f ${GITLAB_RULES_HANDLER} ]; then rm ${GITLAB_RULES_HANDLER}; fi && \
	if [ -f ${GITLAB_RULES_HANDLER_SAMPLE} ]; then rm ${GITLAB_RULES_HANDLER_SAMPLE}; fi && \
	curl --header "PRIVATE-TOKEN: $(GITLAB_TOKEN)" \
	"https://gitlab.efko.ru/api/v4/projects/192/repository/files/src%2Fscripts%2Fgitlab-rules%2Fprepare-commit-msg/raw?ref=master" \
	-o ${GITLAB_RULES_HANDLER} && \
	chmod +x ${GITLAB_RULES_HANDLER} && \
	echo "\033[0;32mОбработчик активирован\033[0m"

# Деактивация для случаев конфликтов именования коммитов при операциях rebase, cherry-pick и т.д.

disable-commit-msg-rule:
	@if [ -f ${GITLAB_RULES_HANDLER} ]; then mv ${GITLAB_RULES_HANDLER} ${GITLAB_RULES_HANDLER_SAMPLE} && echo "\033[0;32mОбработчик деактивирован\033[0m"; else echo "\033[0;33mОбработчик деактивирован ранее\033[0m"; fi

enable-commit-msg-rule:
	@if [ -f ${GITLAB_RULES_HANDLER_SAMPLE} ]; then mv ${GITLAB_RULES_HANDLER_SAMPLE} ${GITLAB_RULES_HANDLER} && echo "\033[0;32mОбработчик активирован\033[0m"; else echo "\033[0;33mОбработчик уже активирован\033[0m"; fi

# =====