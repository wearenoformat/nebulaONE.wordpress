/*
    Dynamically integrates a customizable chat interface (NebulaOne) into a web page,
    fetching necessary configuration data such as branding colors and Agent details
    from specified external endpoints. Requires 'nebulaInstance' to be defined globally.

    © Cloudforce 2024
*/
{
  if (typeof nebulaInstance === 'undefined') throw new Error('nebulaInstance is undefined')

  if (document.querySelector('.nebulaOne.chat-button')) throw new Error('Script may only be run once')

  const loadNebulaOne = async function () {
    const nebulaContainer = document.createElement('div')
    const shadowRoot = nebulaContainer.attachShadow({mode: 'open'})

    // We don't add a class to nebulaContainer here for alignment purposes.
    // The alignment classes will be added directly to the button/dialog.

    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.type = 'text/css'
    link.href = `${nebulaInstance.hostName}/api/external/v1/brands/byGptPublicEndpoint/${nebulaInstance.gptSystem}/brandVariables.css`
    document.head.appendChild(link)

    const localStyles = document.createElement('link')
    localStyles.rel = 'stylesheet'
    localStyles.href = `${nebulaInstance.pluginBaseUrl}/css/style.css`
    shadowRoot.appendChild(localStyles)


    const chatDialog = document.createElement('dialog')
    const chatDialogForm = document.createElement('form')
    const chatDialogHeader = document.createElement('header')
    const chatDialogCloseButton = document.createElement('span')
    const chatDialogTitle = document.createElement('h1')
    const chatDialogMain = document.createElement('article')
    const chatDialogIframe = document.createElement('iframe')

    const chatButton = document.createElement('button')
    const chatButtonIcon = document.createElement('span')
    const chatButtonLabel = document.createElement('span')

    const openChatDialog = () => {
      chatButton.hidePopover()
      chatDialog.showModal()
    }

    const closeChatDialog = () => {
      chatDialog.close()
      chatButton.showPopover()
    }

    chatDialogTitle.className = 'title'
    chatDialogTitle.innerText = nebulaInstance.title

    chatDialogCloseButton.className = 'close-dialog'
    chatDialogCloseButton.innerText = '×'
    chatDialogCloseButton.addEventListener('click', closeChatDialog)

    chatDialogIframe.src = `${nebulaInstance.hostName}/public-chat/${nebulaInstance.gptSystem}/true`

    chatDialogForm.addEventListener('click', (event) => event.stopPropagation())
    ;[chatDialogTitle, chatDialogCloseButton].forEach((c) => chatDialogHeader.appendChild(c))
    chatDialogMain.appendChild(chatDialogIframe)
    ;[chatDialogHeader, chatDialogMain].forEach((c) => chatDialogForm.appendChild(c))

    chatDialog.appendChild(chatDialogForm)
    chatDialog.addEventListener('click', closeChatDialog)
    chatDialog.className = 'nebulaOne chat-dialog'
    // Add 'left' class to dialog if alignLeft is true
    if (nebulaInstance.alignLeft) {
        chatDialog.classList.add('left');
    }


    chatButtonLabel.innerText = ` ${nebulaInstance.title}`

    chatButtonIcon.innerHTML =
    '<span class="circle-1"></span><span class="circle-2"></span><span class="circle-3"></span><span class="circle-4"></span><span class="circle-5"></span><span class="circle-6"></span><span class="circle-7"></span><span class="circle-8"></span><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.3164 17.563H10.7676L9.50781 13.9985H4L2.78906 17.563H0.25L5.49414 3.55909H8.11133L13.3164 17.563ZM8.89258 12.104L6.94922 6.51807C6.89062 6.33578 6.82878 6.04281 6.76367 5.63917H6.72461C6.66602 6.01026 6.60091 6.30323 6.52930 6.51807L4.60547 12.104H8.89258Z" fill="white"/><path d="M17.2617 17.563H14.9961V8.88453C14.9961 8.88453 15.2932 9.32989 16.2559 9.05909C16.8764 8.88453 17.2617 8.28045 17.2617 8.28045V17.563Z" fill="white"/><path d="M13.2041 3.10924C13.0369 2.98533 13.1078 2.72059 13.3146 2.69693L15.5664 2.43943C15.6295 2.43221 15.6868 2.39911 15.7247 2.34803L17.0735 0.526729C17.1974 0.359431 17.4622 0.430369 17.4858 0.637205L17.7433 2.88894C17.7506 2.95209 17.7837 3.00941 17.8347 3.04724L19.656 4.39611C19.8233 4.52001 19.7524 4.78476 19.5456 4.80841L17.2938 5.06592C17.2307 5.07314 17.1734 5.10624 17.1355 5.15731L15.7867 6.97862C15.6628 7.14592 15.398 7.07498 15.3744 6.86814L15.1168 4.61641C15.1096 4.55326 15.0765 4.49594 15.0255 4.45811L13.2041 3.10924Z" fill="white"/></svg>'

    chatButton.popover = 'manual'
    chatButton.className = 'nebulaOne chat-button'
    // Add 'left' class to button if alignLeft is true
    if (nebulaInstance.alignLeft) {
        chatButton.classList.add('left');
    }
    chatButton.style.setProperty('opacity', 0)
    chatButton.addEventListener('click', openChatDialog)

    chatButton.appendChild(chatButtonIcon)
    ;[chatDialog, chatButton].forEach((c) => shadowRoot.appendChild(c))

    document.body.appendChild(nebulaContainer)

    chatButton.showPopover()
  }
  document.readyState === 'complete' ? loadNebulaOne() : window.addEventListener('load', loadNebulaOne)
}